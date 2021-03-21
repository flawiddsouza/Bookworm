<?php

namespace App\Http\Controllers\Resources;

use App\Models\BookType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RESTActions;

class BookTypeController extends Controller
{
    use RESTActions;

    const MODEL = BookType::class;
    const FIELDS = ['name', 'sort_order'];
    const UNIQUE_FIELDS = ['name'];

    public function index()
    {
        return [
            'paginator' => BookType::orderBy('sort_order')->paginate(),
            'unfiltered_total' => BookType::count()
        ];
    }

    public function getBookTypes()
    {
        return BookType::orderBy('sort_order')->get();
    }
}
