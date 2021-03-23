<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Models\BookSeries;
use App\Models\SeriesAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManageSeriesController extends Controller
{
    public function index()
    {
        return [
            'paginator' => Series::selectRaw("
                series.id,
                series.name,
                string_agg(
                    concat(
                        authors.name,
                        CASE WHEN series_authors.role IS NOT NULL THEN CONCAT(' (', series_authors.role, ')') ELSE '' END
                    ),
                    ', '
                ) as author
            ")
            ->leftJoin('series_authors', 'series_authors.series_id', 'series.id')
            ->leftJoin('authors', 'authors.id', 'series_authors.author_id')
            ->groupBy('series.id')
            ->orderBy('series.updated_at', 'DESC')
            ->paginate(50),
            'unfiltered_total' => Series::count()
        ];
    }

    private function stringContains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $seriesId = Series::create([
                'name' => $request->name
            ])->id;

            foreach($request->authors as $author) {
                SeriesAuthor::create([
                    'series_id' => $seriesId,
                    'author_id' => $author['author_id'],
                    'role' => $author['role'] ?? null
                ]);
            }

            foreach($request->books as $book) {
                BookSeries::create([
                    'index' => $book['index'] ?? null,
                    'book_id' => $book['book_id'],
                    'series_id' => $seriesId
                ]);
            }

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "series_id_author_id_unique"')) {
                return response('Duplicate author in series', 400);
            }

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "book_id_unique"')) {
                return response('A book can\'t be added to multiple series', 400);
            }

            return response($e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            Series::where('id', $id)->update([
                'name' => $request->name
            ]);

            if(count($request->authors) > 0) {
                SeriesAuthor::where('series_id', $id)->delete();

                foreach($request->authors as $author) {
                    SeriesAuthor::create([
                        'series_id' => $id,
                        'author_id' => $author['author_id'],
                        'role' => $author['role'] ?? null
                    ]);
                }
            }

            if(count($request->books) > 0) {
                BookSeries::where('series_id', $id)->delete();

                foreach($request->books as $book) {
                    BookSeries::create([
                        'index' => $book['index'] ?? null,
                        'book_id' => $book['book_id'],
                        'series_id' => $id
                    ]);
                }
            }

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "series_id_author_id_unique"')) {
                return response('Duplicate author in series', 400);
            }

            if($this->stringContains($e->getMessage(), 'duplicate key value violates unique constraint "book_id_unique"')) {
                return response('A book can\'t be added to multiple series', 400);
            }

            return response($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        SeriesAuthor::where('series_id', $id)->delete();
        BookSeries::where('series_id', $id)->delete();
        Series::where('id', $id)->delete();
    }

    public function getAuthorsAndBooks($id)
    {
        return [
            'authors' => SeriesAuthor::where('series_id', $id)
            ->join('authors', 'authors.id', 'series_authors.author_id')
            ->select('series_authors.id', 'series_authors.author_id', 'authors.name', 'series_authors.role')
            ->get(),
            'books' => BookSeries::where('series_id', $id)
            ->join('books', 'books.id', 'book_series.book_id')
            ->select('book_series.id', 'book_series.book_id', 'book_series.index', 'books.name')
            ->get()
        ];
    }
}
