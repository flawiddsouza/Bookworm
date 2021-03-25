<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Series;
use Illuminate\Http\Request;

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
            string_agg(
                concat(
                    authors.name,
                    CASE WHEN book_authors.role IS NOT NULL THEN CONCAT(' (', book_authors.role, ')') ELSE '' END
                ),
                ', '
            ) as author
        ")
        ->leftJoin('book_authors', 'book_authors.book_id', 'books.id')
        ->leftJoin('authors', 'authors.id', 'book_authors.author_id')
        ->where('books.name', 'ilike', '%' . $request->q . '%')
        ->orWhere('authors.name', 'ilike', '%' . $request->q . '%')
        ->groupBy('books.id')
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
