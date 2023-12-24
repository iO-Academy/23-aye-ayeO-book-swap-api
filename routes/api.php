<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(BookController::class)->group(function () {
    Route::get('/books', 'getAllPages');
    Route::get('/books/{id}', 'getById');
    Route::get('/books/check-isbn/{isbn}', 'checkIsbn');
    Route::put('/books/claim/{id}', 'claimById');
    Route::put('/books/return/{id}', 'returnById');
    Route::post('/books', 'add');
});

Route::post('/reviews', [ReviewController::class, 'add']);

Route::get('/genres', [GenreController::class, 'getAll']);
