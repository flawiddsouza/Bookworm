<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function getAuthors(Request $request)
    {
        return Author::select('id', 'name')
        ->where('name', 'ilike', '%' . $request->q . '%')
        ->orderBy('name')
        ->limit(10)
        ->get();
    }

    public function getBooks(Request $request)
    {
        return Book::selectRaw("
            books.id,
            books.name,
            book_types.name as book_type,
            string_agg(
                concat(
                    authors.name,
                    CASE WHEN book_authors.role IS NOT NULL THEN CONCAT(' (', book_authors.role, ')') ELSE '' END
                ),
                ', '
            ) as author,
            (SELECT status FROM user_books WHERE user_books.book_id = books.id AND user_books.user_id = ?) as user_status
        ", [Auth::id()])
        ->leftJoin('book_types', 'book_types.id', 'books.book_type_id')
        ->leftJoin('book_authors', 'book_authors.book_id', 'books.id')
        ->leftJoin('authors', 'authors.id', 'book_authors.author_id')
        ->where('books.name', 'ilike', '%' . $request->q . '%')
        ->orWhere('authors.name', 'ilike', '%' . $request->q . '%')
        ->groupBy('books.id', 'book_types.name')
        ->orderBy('name')
        ->limit(10)
        ->get();
    }

    public function getSeries(Request $request)
    {
        return Series::select('id', 'name')
        ->where('name', 'ilike', '%' . $request->q . '%')
        ->orderBy('name')
        ->limit(10)
        ->get();
    }
}
