<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Series;
use App\Models\BookType;
use App\Models\UserBook;
use App\Classes\CSVHelper;
use App\Models\BookAuthor;
use App\Models\BookSeries;
use App\Models\SeriesAuthor;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ImportController extends Controller
{
    public function postImportGoodreadsCSVExport(Request $request)
    {
        if($request->hasFile('goodreadsCSVExport')) {
            $filePath = $request->file('goodreadsCSVExport')->getRealPath();
            $csvRows = CSVHelper::readCsvFileIntoArray($filePath);

            $requiredColumnHeaders = [
                'Title',
                'Author',
                'Additional Authors',
                'My Rating',
                'Date Added',
                'Date Read',
                'Exclusive Shelf',
                'My Review',
                'Private Notes'
            ];

            $validator = CSVHelper::validate($csvRows, $requiredColumnHeaders);

            if($validator !== 'valid') {
                return $validator;
            }

            $bookType = BookType::orderBy('sort_order')->first();
            $userId = Auth::id();

            DB::beginTransaction();

            try {
                foreach($csvRows as $bookToAdd) {
                    preg_match_all('/(.*?) \((.*, \#.*)\)/m', $bookToAdd['Title'], $matches, PREG_SET_ORDER, 0);

                    $bookTitle = count($matches) === 0 ? $bookToAdd['Title'] : $matches[0][1];

                    $book = Book::where('name', $bookTitle)->first();

                    $series = null;

                    if(!$book) {
                        $book = Book::create([
                            'name' => $bookTitle,
                            'book_type_id' => $bookType->id
                        ]);

                        if(count($matches) > 0) {
                            $seriesToAdd = explode('; ', $matches[0][2]);

                            // only the first series the book belongs to is added, as our system only allows one series per book
                            foreach($seriesToAdd as $seriesToAddItem) {
                                $explodedSeriesToAddItem = explode(', #', $seriesToAddItem);
                                $seriesName = $explodedSeriesToAddItem[0];
                                $seriesIndex = $explodedSeriesToAddItem[1];

                                $series = Series::where('name', $seriesName)->first();

                                if(!$series) {
                                    $series = Series::create([
                                        'name' => $seriesName
                                    ]);
                                }

                                BookSeries::create([
                                    'index' => $seriesIndex,
                                    'book_id' => $book->id,
                                    'series_id' => $series->id
                                ]);

                                break;
                            }
                        }

                        $additionalAuthors = $bookToAdd['Additional Authors'] ? explode(', ', $bookToAdd['Additional Authors']) : [];
                        $authors = array_merge([$bookToAdd['Author']], $additionalAuthors);

                        foreach($authors as $authorToAdd) {
                            $author = Author::where('name', $authorToAdd)->first();

                            if(!$author) {
                                $author = Author::create([
                                    'name' => $authorToAdd
                                ]);
                            }

                            BookAuthor::create([
                                'book_id' => $book->id,
                                'author_id' => $author->id
                            ]);

                            if($series) {
                                SeriesAuthor::updateOrCreate([
                                    'series_id' => $series->id,
                                    'author_id' => $author->id
                                ]);
                            }
                        }
                    }

                    // skip if book already exists for user - as it might overwrite new data added through Bookworm
                    if(UserBook::where('user_id', $userId)->where('book_id', $book->id)->count() > 0) {
                        continue;
                    }

                    $status = null;

                    if($bookToAdd['Exclusive Shelf'] === 'to-read') {
                        $status = 'TO_READ';
                        $bookToAdd['Date Added'] = null;
                        $bookToAdd['Date Read'] = null;
                        $bookToAdd['My Rating'] = null;
                    }

                    if($bookToAdd['Exclusive Shelf'] === 'currently-reading') {
                        $status = 'CURRENTLY_READING';
                        $bookToAdd['Date Read'] = null;
                        $bookToAdd['My Rating'] = null;
                    }

                    if($bookToAdd['Exclusive Shelf'] === 'read') {
                        $status = 'READ';
                        if($bookToAdd['Date Read'] === '') {
                            $bookToAdd['Date Read'] = null;
                        }
                    }

                    UserBook::create([
                        'user_id' => $userId,
                        'book_id' => $book->id,
                        'private_notes' => $bookToAdd['Private Notes'],
                        'public_notes' => $bookToAdd['My Review'],
                        'rating' => $bookToAdd['My Rating'],
                        'status' => $status,
                        'started_reading' => $bookToAdd['Date Added'],
                        'completed_reading' => $bookToAdd['Date Read']
                    ]);
                }

                DB::commit();
            } catch(\Throwable $e) {
                DB::rollBack();

                if(Str::contains($e->getMessage(), 'duplicate key value violates unique constraint "user_id_book_id_unique"')) {
                    return response('Possible double entries in file being imported for book ' . $bookToAdd['Title'] . '. Go through the file and remove the duplicate and keep the row you want.', 400);
                }

                return response(json_encode($bookToAdd) . ' - ' . $e->getMessage(), 500);
            }
        }
    }
}
