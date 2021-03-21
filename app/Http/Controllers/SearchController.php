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
        return Book::select('id', 'name')
        ->where('name', 'ilike', '%' . $request->q . '%')
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
