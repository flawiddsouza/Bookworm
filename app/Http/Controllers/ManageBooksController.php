<?php

namespace App\Http\Controllers;

use App\Classes\Formatter;
use App\Models\Book;
use App\Classes\Paginator;
use App\Models\BookAuthor;
use App\Models\BookSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManageBooksController extends Controller
{
    public function index(Request $request)
    {
        $bookColumn = "CASE WHEN series.name IS NOT NULL THEN CONCAT(books.name, ' (', series.name, ' #', book_series.index, ')') ELSE books.name END";

        $result = Paginator::generate(
            Book::selectRaw("
                books.id,
                books.name,
                books.book_type_id,
                book_types.name as book_type,
                books.cover_image_url,
                MIN(book_series.series_id) as series_id,
                CASE
                    WHEN COUNT(book_series.series_id) > 0 THEN
                        json_agg(
                            json_build_object(
                                'name', series.name,
                                'index', book_series.index
                            )
                        ) FILTER (WHERE series.name IS NOT NULL)
                    ELSE '[]'::json
                END as series_info,
                string_agg(
                    concat(
                        authors.name,
                        CASE WHEN book_authors.role IS NOT NULL THEN CONCAT(' (', book_authors.role, ')') ELSE '' END
                    ),
                    ', '
                    ORDER BY book_authors.id
                ) as author
            ")
            ->join('book_types', 'book_types.id', 'books.book_type_id')
            ->leftJoin('book_authors', 'book_authors.book_id', 'books.id')
            ->leftJoin('authors', 'authors.id', 'book_authors.author_id')
            ->leftJoin('book_series', 'book_series.book_id', 'books.id')
            ->leftJoin('series', 'series.id', 'book_series.series_id')
            ->groupBy('books.id', 'book_types.id'),
            [
                'sortBy' => 'books.updated_at',
                'sortOrder' => 'DESC',
                'filterColumns' => [
                    DB::raw($bookColumn),
                    'authors.name'
                ]
            ],
            $request
        );

        $result['paginator']->transform(function($item) {
            $item->series_info = $item->series_info ? json_decode($item->series_info, true) : [];

            // Remove duplicates from series_info
            if (!empty($item->series_info)) {
                $unique_series = [];
                $seen = [];

                foreach ($item->series_info as $series) {
                    $key = $series['name'] . '_' . $series['index'];
                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $unique_series[] = $series;
                    }
                }

                usort($unique_series, function($a, $b) {
                    return $a['index'] <=> $b['index'];
                });

                $item->series_info = $unique_series;
            }

            // Remove duplicate authors while maintaining order
            if (!empty($item->author)) {
                $authors = explode(', ', $item->author);
                $unique_authors = [];
                $seen_authors = [];

                foreach ($authors as $author) {
                    if (!isset($seen_authors[$author])) {
                        $seen_authors[$author] = true;
                        $unique_authors[] = $author;
                    }
                }

                $item->author = implode(', ', $unique_authors);
            }

            $item->series_display_name = Formatter::formatSeriesInfo($item->series_info);
            $item->display_name = $item->name;

            if (!empty($item->series_display_name)) {
                $item->display_name .= ' (' . $item->series_display_name . ')';
            }

            return $item;
        });

        return $result;
    }

    private function stringContains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $bookId = Book::create([
                'name' => $request->name,
                'book_type_id' => $request->book_type_id,
                'cover_image_url' => $request->cover_image_url
            ])->id;

            foreach($request->authors as $author) {
                BookAuthor::create([
                    'book_id' => $bookId,
                    'author_id' => $author['author_id'],
                    'role' => $author['role'] ?? null
                ]);
            }

            if(isset($request->series) && count($request->series) > 0) {
                foreach($request->series as $series) {
                    BookSeries::create([
                        'index' => $series['index'] ?? null,
                        'book_id' => $bookId,
                        'series_id' => $series['series_id']
                    ]);
                }
            }

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "book_id_author_id_unique"')) {
                return response('Duplicate author in book', 400);
            }

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "book_id_series_id_unique"')) {
                return response('Duplicate series entry: same book cannot be added to the same series with the same index', 400);
            }

            return response($e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            Book::where('id', $id)->update([
                'name' => $request->name,
                'book_type_id' => $request->book_type_id,
                'cover_image_url' => $request->cover_image_url
            ]);

            if(count($request->authors) > 0) {
                BookAuthor::where('book_id', $id)->delete();

                foreach($request->authors as $author) {
                    BookAuthor::create([
                        'book_id' => $id,
                        'author_id' => $author['author_id'],
                        'role' => $author['role'] ?? null
                    ]);
                }
            }

            BookSeries::where('book_id', $id)->delete();
            if(isset($request->series) && count($request->series) > 0) {
                foreach($request->series as $series) {
                    BookSeries::create([
                        'index' => $series['index'] ?? null,
                        'book_id' => $id,
                        'series_id' => $series['series_id']
                    ]);
                }
            }

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "book_id_author_id_unique"')) {
                return response('Duplicate author in book', 400);
            }

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "book_id_series_id_unique"')) {
                return response('Duplicate series entry: same book cannot be added to the same series with the same index', 400);
            }

            return response($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            BookSeries::where('book_id', $id)->delete();
            BookAuthor::where('book_id', $id)->delete();
            Book::where('id', $id)->delete();

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();

            return response('Entry in use', 500);
        }
    }

    public function getAuthors($id)
    {
        return BookAuthor::where('book_id', $id)
        ->join('authors', 'authors.id', 'book_authors.author_id')
        ->select('book_authors.id', 'book_authors.author_id', 'authors.name', 'book_authors.role')
        ->get();
    }

    public function getSeries($id)
    {
        return BookSeries::where('book_id', $id)
        ->join('series', 'series.id', 'book_series.series_id')
        ->select('book_series.id', 'book_series.series_id', 'series.name', 'book_series.index')
        ->orderBy('book_series.index')
        ->get();
    }

    public function updateBookType(Request $request, $id)
    {
        try {
            $request->validate([
                'book_type_id' => 'required|exists:book_types,id'
            ]);

            Book::where('id', $id)->update([
                'book_type_id' => $request->book_type_id
            ]);

            return response()->json(['message' => 'Book type updated successfully']);
        } catch(\Throwable $e) {
            return response($e->getMessage(), 500);
        }
    }

    public function getAuthorsAndSeries($id)
    {
        return [
            'authors' => $this->getAuthors($id),
            'series' => $this->getSeries($id)
        ];
    }
}
