<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function getAll()
    {
        return response()->json([
            'data' => Book::with('genre:id,name')->get()->makeHidden(['genre_id', 'created_at', 'updated_at', 'blurb', 'page_count', 'year', 'review_id', 'claimed_by_email', 'claimed_by_name']),
            'message' => 'Books successfully retrieved'
        ]);
    }

    public function claim()
    {
        return response()->json(['test']);
    }
}
