<?php

namespace App\Http\Controllers;

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

        return Paginator::generate(
            Book::selectRaw("
                books.id,
                books.name,
                $bookColumn as display_name,
                books.book_type_id,
                book_types.name as book_type,
                books.cover_image_url,
                book_series.series_id as series_id,
                book_series.index as series_index,
                series.name as series_name,
                string_agg(
                    concat(
                        authors.name,
                        CASE WHEN book_authors.role IS NOT NULL THEN CONCAT(' (', book_authors.role, ')') ELSE '' END
                    ),
                    ', '
                ) as author
            ")
            ->join('book_types', 'book_types.id', 'books.book_type_id')
            ->leftJoin('book_authors', 'book_authors.book_id', 'books.id')
            ->leftJoin('authors', 'authors.id', 'book_authors.author_id')
            ->leftJoin('book_series', 'book_series.book_id', 'books.id')
            ->leftJoin('series', 'series.id', 'book_series.series_id')
            ->groupBy('books.id', 'book_types.id', 'book_series.id', 'series.id'),
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

            if(isset($request->series_id)) {
                BookSeries::create([
                    'index' => $request->series_index ?? null,
                    'book_id' => $bookId,
                    'series_id' => $request->series_id
                ]);
            }

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "book_id_author_id_unique"')) {
                return response('Duplicate author in book', 400);
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

            if(isset($request->series_id)) {
                BookSeries::updateOrCreate([
                    'book_id' => $id
                ], [
                    'index' => $request->series_index ?? null,
                    'series_id' => $request->series_id
                ]);
            } else {
                BookSeries::where('book_id', $id)->delete();
            }

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "book_id_author_id_unique"')) {
                return response('Duplicate author in book', 400);
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
}
