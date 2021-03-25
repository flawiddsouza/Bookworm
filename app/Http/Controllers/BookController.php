<?php

namespace App\Http\Controllers;

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

        $bookColumn = "CASE WHEN series.name IS NOT NULL THEN CONCAT(books.name, ' (', series.name, ' #', book_series.index, ')') ELSE books.name END";

        return Book::selectRaw("
            books.id,
            $bookColumn as book,
            book_types.name as book_type,
            books.cover_image_url,
            string_agg(
                concat(
                    authors.name,
                    CASE WHEN book_authors.role IS NOT NULL THEN CONCAT(' (', book_authors.role, ')') ELSE '' END
                ),
                ', '
            ) as author,
            user_books.status,
            user_books.started_reading,
            to_char(user_books.started_reading, '$sqlDateFormat') as started_reading_display,
            user_books.completed_reading,
            to_char(user_books.completed_reading, '$sqlDateFormat') as completed_reading_display,
            user_books.rating,
            CASE WHEN user_books.rating IS NOT NULL THEN CONCAT(user_books.rating, '/', 5) ELSE null END as rating_display,
            user_books.private_notes,
            user_books.public_notes
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
        ->groupBy('user_books.id', 'series.id', 'books.id', 'book_series.id', 'book_types.id')
        ->first();
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
                'private_notes' => $request->private_notes,
                'public_notes' => $request->public_notes,
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
