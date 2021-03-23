<?php

namespace App\Http\Controllers\Resources;

use App\Models\Author;
use App\Classes\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RESTActions;

class AuthorController extends Controller
{
    use RESTActions;

    const MODEL = Author::class;
    const FIELDS = ['name'];
    const UNIQUE_FIELDS = ['name'];

    public function index(Request $request)
    {
        return Paginator::generate(
            Author::select('id', 'name'),
            [
                'sortBy' => 'updated_at',
                'sortOrder' => 'DESC',
                'filterColumns' => [
                    'name'
                ]
            ],
            $request
        );
    }
}
