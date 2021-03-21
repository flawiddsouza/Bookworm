<?php

namespace App\Http\Controllers\Resources;

use App\Models\Author;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RESTActions;

class AuthorController extends Controller
{
    use RESTActions;

    const MODEL = Author::class;
    const FIELDS = ['name'];
    const UNIQUE_FIELDS = ['name'];

    public function index()
    {
        return [
            'paginator' => Author::orderBy('updated_at', 'DESC')->paginate(),
            'unfiltered_total' => Author::count()
        ];
    }
}
