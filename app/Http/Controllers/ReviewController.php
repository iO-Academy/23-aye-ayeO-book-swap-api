<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:0|max:5',
            'review' => 'required|string|min:10|max:10000',
            'book_id' => 'required|integer|min:1|exists:books,id',
        ]);

        $name = $request->name;
        $rating = $request->rating;
        $review = $request->review;
        $book_id = $request->book_id;

        $newReview = new Review();

        $newReview->name = $name;
        $newReview->rating = $rating;
        $newReview->review = $review;
        $newReview->book_id = $book_id;

        if ($newReview->save()) {
            return response()->json([
                'message' => 'Review created'
            ], 201);
        }

        return response()->json([
            'message' => 'Unexpected error occurred'
        ], 500);
    }
}
