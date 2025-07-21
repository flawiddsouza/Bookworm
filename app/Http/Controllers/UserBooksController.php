<?php

namespace App\Http\Controllers;

use App\Models\UserBook;
use App\Classes\Paginator;
use App\Classes\Formatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserBooksController extends Controller
{
    private function getSortByConfig($status)
    {
        switch ($status) {
            case 'READ':
                return [
                    [
                        'column' => 'user_books.completed_reading',
                        'direction' => 'desc'
                    ],
                    [
                        'column' => 'user_books.updated_at',
                        'direction' => 'desc'
                    ]
                ];

            case 'CURRENTLY_READING,READ':
                return [
                    [
                        'column' => 'user_books.completed_reading',
                        'direction' => 'desc'
                    ],
                    [
                        'column' => 'user_books.started_reading',
                        'direction' => 'desc'
                    ],
                    [
                        'column' => 'user_books.updated_at',
                        'direction' => 'desc'
                    ]
                ];

            default:
                return [
                    [
                        'column' => 'user_books.updated_at',
                        'direction' => 'desc'
                    ]
                ];
        }
    }

    public function index(Request $request)
    {
        $sqlDateFormat = env('SQL_DATE_FORMAT');

        $result = Paginator::generate(
            UserBook::selectRaw("
                user_books.id,
                books.name,
                book_types.name as book_type,
                books.cover_image_url,
                CASE
                    WHEN COUNT(book_series.series_id) > 0 THEN
                        json_agg(
                            json_build_object(
                                'name', series.name,
                                'index', book_series.index
                            )
                        ) FILTER (WHERE series.name IS NOT NULL)
                    ELSE '[]'::json
                END as series_info,
                string_agg(
                    concat(
                        authors.name,
                        CASE WHEN book_authors.role IS NOT NULL THEN CONCAT(' (', book_authors.role, ')') ELSE '' END
                    ),
                    ', '
                    ORDER BY book_authors.id
                ) as author,
                user_books.status,
                user_books.started_reading,
                to_char(user_books.started_reading, '$sqlDateFormat') as started_reading_display,
                user_books.completed_reading,
                to_char(user_books.completed_reading, '$sqlDateFormat') as completed_reading_display,
                user_books.rating,
                CASE WHEN user_books.rating IS NOT NULL THEN CONCAT(user_books.rating, '/', 5) ELSE null END as rating_display,
                user_books.notes,
                user_books.reading_medium,
                user_books.book_id
            ")
            ->join('books', 'books.id', 'user_books.book_id')
            ->join('book_types', 'book_types.id', 'books.book_type_id')
            ->leftJoin('book_authors', 'book_authors.book_id', 'books.id')
            ->leftJoin('authors', 'authors.id', 'book_authors.author_id')
            ->leftJoin('book_series', 'book_series.book_id', 'books.id')
            ->leftJoin('series', 'series.id', 'book_series.series_id')
            ->where(function($query) use ($request) {
                if (str_contains($request->status, ',')) {
                    $statuses = explode(',', $request->status);
                    $query->whereIn('user_books.status', $statuses);
                } else {
                    $query->where('user_books.status', $request->status);
                }
            })
            ->when($request->book_type, function($query) use ($request) {
                if (str_contains($request->book_type, ',')) {
                    $bookTypeIds = explode(',', $request->book_type);
                    $query->whereIn('books.book_type_id', $bookTypeIds);
                } else {
                    $query->where('books.book_type_id', $request->book_type);
                }
            })
            ->where('user_books.user_id', Auth::id())
            ->groupBy('user_books.id', 'books.id', 'book_types.id'),
            [
                'sortBy' => $this->getSortByConfig($request->status),
                'filterColumns' => [
                    'books.name',
                    'authors.name'
                ],
                'requestSortBySubtitutions' => [
                    'started_reading_display' => 'started_reading',
                    'completed_reading_display' => 'completed_reading'
                ]
            ],
            $request
        );

        $result['paginator']->transform(function($item) {
            $item->series_info = $item->series_info ? json_decode($item->series_info, true) : [];

            // Remove duplicates from series_info
            if (!empty($item->series_info)) {
                $unique_series = [];
                $seen = [];

                foreach ($item->series_info as $series) {
                    $key = $series['name'] . '_' . $series['index'];
                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $unique_series[] = $series;
                    }
                }

                usort($unique_series, function($a, $b) {
                    return $a['index'] <=> $b['index'];
                });

                $item->series_info = $unique_series;
            }

            // Remove duplicate authors while maintaining order
            if (!empty($item->author)) {
                $authors = explode(', ', $item->author);
                $unique_authors = [];
                $seen_authors = [];

                foreach ($authors as $author) {
                    if (!isset($seen_authors[$author])) {
                        $seen_authors[$author] = true;
                        $unique_authors[] = $author;
                    }
                }

                $item->author = implode(', ', $unique_authors);
            }

            $item->series_display_name = Formatter::formatSeriesInfo($item->series_info);
            $item->display_name = $item->name;

            if (!empty($item->series_display_name)) {
                $item->display_name .= ' (' . $item->series_display_name . ')';
            }

            // Update the book field to use the new display_name for backward compatibility
            $item->book = $item->display_name;

            return $item;
        });

        return $result;
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            UserBook::where('id', $id)->where('user_id', Auth::id())->update([
                'reading_medium' => $request->reading_medium,
                'notes' => $request->notes,
                'rating' => $request->rating,
                'status' => $request->status,
                'started_reading' => $request->started_reading,
                'completed_reading' => $request->completed_reading
            ]);

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollBack();

            return response($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        UserBook::where('id', $id)->where('user_id', Auth::id())->delete();
    }
}
