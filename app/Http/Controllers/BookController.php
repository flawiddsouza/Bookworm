<?php

namespace App\Http\Controllers;

use App\Classes\Formatter;
use App\Models\Book;
use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function getBook($id)
    {
        $sqlDateFormat = env('SQL_DATE_FORMAT');

        $book = Book::selectRaw("
            books.id,
            books.name,
            book_types.name as book_type,
            books.cover_image_url,
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
            ) as author,
            user_books.status,
            user_books.started_reading,
            to_char(user_books.started_reading, '$sqlDateFormat') as started_reading_display,
            user_books.completed_reading,
            to_char(user_books.completed_reading, '$sqlDateFormat') as completed_reading_display,
            user_books.rating,
            CASE WHEN user_books.rating IS NOT NULL THEN CONCAT(user_books.rating, '/', 5) ELSE null END as rating_display,
            user_books.notes,
            user_books.reading_medium
        ")
        ->leftJoin('user_books', function($join) {
            $join->on('user_books.book_id', 'books.id');
            $join->where('user_books.user_id', Auth::id());
        })->join('book_types', 'book_types.id', 'books.book_type_id')
        ->leftJoin('book_authors', 'book_authors.book_id', 'books.id')
        ->leftJoin('authors', 'authors.id', 'book_authors.author_id')
        ->leftJoin('book_series', 'book_series.book_id', 'books.id')
        ->leftJoin('series', 'series.id', 'book_series.series_id')
        ->where('books.id', $id)
        ->groupBy('user_books.id', 'books.id', 'book_types.id')
        ->first();

        if ($book) {
            $book->series_info = $book->series_info ? json_decode($book->series_info, true) : [];

            // Remove duplicates from series_info
            if (!empty($book->series_info)) {
                $unique_series = [];
                $seen = [];

                foreach ($book->series_info as $series) {
                    $key = $series['name'] . '_' . $series['index'];
                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $unique_series[] = $series;
                    }
                }

                usort($unique_series, function($a, $b) {
                    return $a['index'] <=> $b['index'];
                });

                $book->series_info = $unique_series;
            }

            // Remove duplicate authors while maintaining order
            if (!empty($book->author)) {
                $authors = explode(', ', $book->author);
                $unique_authors = [];
                $seen_authors = [];

                foreach ($authors as $author) {
                    if (!isset($seen_authors[$author])) {
                        $seen_authors[$author] = true;
                        $unique_authors[] = $author;
                    }
                }

                $book->author = implode(', ', $unique_authors);
            }

            $book->series_display_name = Formatter::formatSeriesInfo($book->series_info);
            $book->display_name = $book->name;

            if (!empty($book->series_display_name)) {
                $book->display_name .= ' (' . $book->series_display_name . ')';
            }

            // Update the book field to use the new display_name for backward compatibility
            $book->book = $book->display_name;
        }

        return $book;
    }

    public function postBook(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            UserBook::updateOrCreate([
                'user_id' => Auth::id(),
                'book_id' => $id
            ],
            [
                'reading_medium' => $request->reading_medium,
                'notes' => $request->notes,
                'rating' => $request->rating,
                'status' => $request->status,
                'started_reading' => $request->started_reading,
                'completed_reading' => $request->completed_reading
            ]);

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();

            return response($e->getMessage(), 500);
        }
    }
}
