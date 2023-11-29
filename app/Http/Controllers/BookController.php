<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Rules\IsbnRule;

class BookController extends Controller
{
    public function getAll(Request $request)
    {
        $request->validate([
            'claimed' => 'nullable|numeric|min:0|max:1',
            'genre' => 'nullable|numeric|min:0' . ($request->query('genre') == 0 ? '' : '|exists:genres,id'),
            'search' => 'nullable|string'
        ]);

        $hidden =
            [
                'genre_id',
                'created_at',
                'updated_at',
                'blurb',
                'page_count',
                'year',
                'claimed_by_name',
                'claimed_by_email',
                'claimed',
                'isbn10',
                'isbn13',
                'language'
            ];

        $claimed = $request->query('claimed');
        $genre = $request->query('genre');
        $search = $request->query('search');

        $books = Book::with('genre:id,name')

            ->when(
                $search !== null,
                function ($query) use ($search) {
                    return $query->where(function ($query) use ($search) {
                        return $query
                            ->where('title', 'LIKE', '%' . $search . '%')
                            ->orWhere('author', 'LIKE', '%' . $search . '%')
                            ->orWhere('blurb', 'LIKE', '%' . $search . '%');
                    });
                }
            )
            ->when(
                $claimed !== null,
                function ($query) use ($claimed) {
                    return $query
                        ->where('claimed', $claimed);
                }
            )
            ->when(
                $genre !== null,
                function ($query) use ($genre) {
                    if ($genre == 0) {
                        return $query;
                    }
                    return $query->where('genre_id', $genre);
                }
            )
            ->get()
            ->makeHidden($hidden);

        if (!count($books)) {
            return response()->json([
                'message' => "No books found"
            ], 404);
        }

        return response()->json([
            'data' => $books,
            'message' => 'Books successfully retrieved'
        ]);
    }


    public function getById(int|string $id)
    {

        $book = Book::with(['genre:id,name', 'reviews'])->find($id);

        if ($book) {
            $book->makeHidden([
                'genre_id',
                'created_at',
                'updated_at',
                'claimed_by_email',
                'claimed',

            ]);
            $book->reviews->makeHidden(['book_id', 'created_at', 'updated_at']);

            return response()->json([
                'data' => $book,
                'message' => 'Book successfully found'
            ]);
        }

        return response()->json([
            'message' => "Book with id $id not found"
        ], 404);
    }

    public function claimById(int|string $id, Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email'
        ]);

        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => "Book $id was not found"
            ], 404);
        }

        if ($book->claimed_by_name) {
            return response()->json([
                'message' => "Book $id is already claimed"
            ], 400);
        }

        $book->claimed_by_name = $request->name;
        $book->claimed_by_email = $request->email;
        $book->claimed = 1;
        $book->save();

        return response()->json([
            'message' => "Book $id was claimed"
        ]);
    }

    public function returnById(int|string $id, Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'message' => "Book $id was not found"
            ], 404);
        }

        if (!$book->claimed_by_name) {
            return response()->json([
                'message' => "Book $id is not currently claimed"
            ], 400);
        }

        if ($book->claimed_by_email !== $request->email) {
            return response()->json([
                'message' => "Book $id was not returned. $request->email did not claim this book."
            ], 400);
        }

        $book->claimed_by_name = null;
        $book->claimed_by_email = null;
        $book->claimed = 0;
        if ($book->save()) {
            return response()->json([
                'message' => "Book $id was returned"
            ]);
        }

        return response()->json([
            'message' => "Book $id was not able to be returned"
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'genre_id' => 'required|integer|min:1',
            'blurb' => 'required|string|max:10000',
            'image' => 'required|url|max:999',
            'year' => 'required|integer',
            'isbn10' => ['required', 'string', 'unique:books,isbn10', new IsbnRule],
            'isbn13' => ['required', 'string', 'unique:books,isbn13', new IsbnRule],
            'language' => 'required|string|max:2'
        ]);

        $newBook = new Book();

        $newBook->title = $request->title;
        $newBook->author = $request->author;
        $newBook->genre_id = $request->genre_id;
        $newBook->blurb = $request->blurb;
        $newBook->image = $request->image;
        $newBook->year = $request->year;
        $newBook->isbn10 = $request->isbn10;
        $newBook->isbn13 = $request->isbn13;
        $newBook->language = $request->language;

        if ($newBook->save()) {
            return response()->json([
                'message' => 'Book created'
            ], 201);
        }

        return response()->json([
            'message' => 'Unexpected error occurred'
        ], 500);
    }

    public function checkIsbn($isbn)
    {
        $exists = Book::where('isbn10', $isbn)
            ->orWhere('isbn13', $isbn)
            ->exists();

        return response()->json(['exists' => $exists]);
    }
}
