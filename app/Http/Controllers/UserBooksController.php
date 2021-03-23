<?php

namespace App\Http\Controllers;

use App\Models\UserBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBooksController extends Controller
{
    public function index(Request $request)
    {
        $sqlDateFormat = env('SQL_DATE_FORMAT');

        return [
            'paginator' => UserBook::selectRaw("
                user_books.id,
                CASE WHEN series.name IS NOT NULL THEN CONCAT(books.name, ' (', series.name, ' #', book_series.index, ')') ELSE books.name END as book,
                book_types.name as book_type,
                books.cover_image_url,
                string_agg(
                    concat(
                        authors.name,
                        CASE WHEN book_authors.role IS NOT NULL THEN CONCAT(' (', book_authors.role, ')') ELSE '' END
                    ),
                    ', '
                ) as author,
                to_char(user_books.started_reading, '$sqlDateFormat') as started_reading,
                to_char(user_books.completed_reading, '$sqlDateFormat') as completed_reading
            ")
            ->join('books', 'books.id', 'user_books.book_id')
            ->join('book_types', 'book_types.id', 'books.book_type_id')
            ->leftJoin('book_authors', 'book_authors.book_id', 'books.id')
            ->leftJoin('authors', 'authors.id', 'book_authors.author_id')
            ->leftJoin('book_series', 'book_series.book_id', 'books.id')
            ->leftJoin('series', 'series.id', 'book_series.series_id')
            ->where('user_books.status', $request->status)
            ->where('user_books.user_id', Auth::id())
            ->groupBy('user_books.id', 'series.id', 'books.id', 'book_series.id', 'book_types.id')
            ->orderBy('books.updated_at', 'DESC')
            ->paginate(50),
            'unfiltered_total' => UserBook::where('user_books.status', $request->status)
            ->where('user_books.user_id', Auth::id())
            ->count()
        ];
    }

    public function destroy($id)
    {
        UserBook::where('id', $id)->delete();
    }
}
