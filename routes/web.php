<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ManageBooksController;
use App\Http\Controllers\ManageSeriesController;
use App\Http\Controllers\Resources\AuthorController;
use App\Http\Controllers\Resources\BookTypeController;

Route::get('/', function () {
    return view('app');
})->middleware('auth');

Route::group([ 'prefix' => '/json', 'middleware' => 'auth'], function() {
    Route::resource('/manage-books', ManageBooksController::class);
    Route::get('/manage-books/{id}/authors', [ManageBooksController::class, 'getAuthors']);

    Route::resource('/manage-series', ManageSeriesController::class);
    Route::get('/manage-series/{id}/authors-and-books', [ManageSeriesController::class, 'getAuthorsAndBooks']);

    Route::resource('/manage-authors', AuthorController::class);
    Route::resource('/manage-book-types', BookTypeController::class);

    Route::get('/search/authors', [SearchController::class, 'getAuthors']);
    Route::get('/search/books', [SearchController::class, 'getBooks']);
    Route::get('/search/series', [SearchController::class, 'getSeries']);

    Route::get('/book-types', [BookTypeController::class, 'getBookTypes']);

    Route::post('/import/goodreads-csv-export', [ImportController::class, 'postImportGoodreadsCSVExport']);
});
