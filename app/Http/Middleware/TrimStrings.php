<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'notes', // don't trim notes as they may contain leading/trailing spaces intentionally
        'current_password',
        'password',
        'password_confirmation',
    ];
}
