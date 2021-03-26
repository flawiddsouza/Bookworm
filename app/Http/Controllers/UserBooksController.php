<?php

namespace App\Http\Controllers;

use App\Models\UserBook;
use App\Classes\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserBooksController extends Controller
{
    public function index(Request $request)
    {
        $sqlDateFormat = env('SQL_DATE_FORMAT');

        $bookColumn = "CASE WHEN series.name IS NOT NULL THEN CONCAT(books.name, ' (', series.name, ' #', book_series.index, ')') ELSE books.name END";

        $query = UserBook::selectRaw("
            user_books.id,
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
            user_books.public_notes,
            user_books.reading_medium
        ")
        ->join('books', 'books.id', 'user_books.book_id')
        ->join('book_types', 'book_types.id', 'books.book_type_id')
        ->leftJoin('book_authors', 'book_authors.book_id', 'books.id')
        ->leftJoin('authors', 'authors.id', 'book_authors.author_id')
        ->leftJoin('book_series', 'book_series.book_id', 'books.id')
        ->leftJoin('series', 'series.id', 'book_series.series_id')
        ->where('user_books.status', $request->status)
        ->where('user_books.user_id', Auth::id())
        ->groupBy('user_books.id', 'series.id', 'books.id', 'book_series.id', 'book_types.id');

        return Paginator::generate(
            $query,
            [
                'sortBy' => $request->status === 'READ' ? 'user_books.completed_reading' : 'user_books.updated_at',
                'sortOrder' => 'DESC',
                'filterColumns' => [
                    DB::raw($bookColumn),
                    'authors.name'
                ],
                'requestSortBySubtitutions' => [
                    'started_reading_display' => 'started_reading',
                    'completed_reading_display' => 'completed_reading'
                ]
            ],
            $request
        );
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            UserBook::where('id', $id)->where('user_id', Auth::id())->update([
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

    public function destroy($id)
    {
        UserBook::where('id', $id)->where('user_id', Auth::id())->delete();
    }
}
