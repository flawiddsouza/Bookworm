<?php

namespace App\Http\Controllers\Resources;

use App\Models\BookType;
use App\Classes\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RESTActions;

class BookTypeController extends Controller
{
    use RESTActions;

    const MODEL = BookType::class;
    const FIELDS = ['name', 'sort_order'];
    const UNIQUE_FIELDS = ['name'];

    public function index(Request $request)
    {
        return Paginator::generate(
            BookType::select('id', 'name', 'sort_order'),
            [
                'sortBy' => 'sort_order',
                'sortOrder' => 'ASC',
                'filterColumns' => [
                    'name'
                ]
            ],
            $request
        );
    }

    public function getBookTypes()
    {
        return BookType::orderBy('sort_order')->get();
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:book_types,id',
            'order.*.sort_order' => 'required|integer|min:1'
        ]);

        foreach ($request->order as $item) {
            BookType::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Order updated successfully']);
    }
}
