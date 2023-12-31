<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

use function PHPUnit\Framework\assertJson;

class BookTest extends TestCase
{
    use DatabaseMigrations;

    public function test_get_all_books_success(): void
    {
        Book::factory()->count(20)->create();

        $response = $this->getJson('/api/books');
        $response->dump();


        $response->assertStatus(200)

            ->assertJson(function (AssertableJson $json) {

                $json->hasAll(['message', 'data'])
                    ->whereAllType([
                        'message' => 'string'
                    ])
                    ->has('data', 20, function (AssertableJson $json) {
                        $json->hasAll(['id', 'title', 'author', 'image', 'genre'])
                            ->whereAllType([
                                'id' => 'integer',
                                'title' => 'string',
                                'author' => 'string',
                                'image' => 'string'
                            ])
                            ->has('genre', function (AssertableJson $json) {
                                $json->hasAll(['id', 'name'])
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string'
                                    ]);
                            });
                    });
            });
    }

    public function test_get_all_books_filter_unclaimed_success(): void
    {
        Book::factory(['claimed' => 0])->count(2)->create();
        Book::factory(['claimed' => 1])->count(2)->create();

        $response = $this->getJson('/api/books?claimed=0');

        $response->assertStatus(200)

            ->assertJson(function (AssertableJson $json) {

                $json->hasAll(['message', 'data'])
                    ->whereAllType([
                        'message' => 'string'
                    ])
                    ->has('data', 2, function (AssertableJson $json) {
                        $json->hasAll(['id', 'title', 'author', 'image', 'genre'])
                            ->whereAllType([
                                'id' => 'integer',
                                'title' => 'string',
                                'author' => 'string',
                                'image' => 'string'
                            ])
                            ->has('genre', function (AssertableJson $json) {
                                $json->hasAll(['id', 'name'])
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string'
                                    ]);
                            });
                    });
            });
    }

    public function test_get_all_books_filter_claimed_success(): void
    {
        Book::factory(['claimed' => 0])->count(2)->create();
        Book::factory(['claimed' => 1])->count(2)->create();

        $response = $this->getJson('/api/books?claimed=1');

        $response->assertStatus(200)

            ->assertJson(function (AssertableJson $json) {

                $json->hasAll(['message', 'data'])
                    ->whereAllType([
                        'message' => 'string'
                    ])
                    ->has('data', 2, function (AssertableJson $json) {
                        $json->hasAll(['id', 'title', 'author', 'image', 'genre'])
                            ->whereAllType([
                                'id' => 'integer',
                                'title' => 'string',
                                'author' => 'string',
                                'image' => 'string'
                            ])
                            ->has('genre', function (AssertableJson $json) {
                                $json->hasAll(['id', 'name'])
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string'
                                    ]);
                            });
                    });
            });
    }
    public function test_get_all_books_filter_genre_success(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/books?genre=$book->genre_id");

        $response->assertStatus(200)

            ->assertJson(function (AssertableJson $json) {

                $json->hasAll(['message', 'data'])
                    ->whereAllType([
                        'message' => 'string'
                    ])
                    ->has('data', 1, function (AssertableJson $json) {
                        $json->hasAll(['id', 'title', 'author', 'image', 'genre'])
                            ->whereAllType([
                                'id' => 'integer',
                                'title' => 'string',
                                'author' => 'string',
                                'image' => 'string'
                            ])
                            ->has('genre', function (AssertableJson $json) {
                                $json->hasAll(['id', 'name'])
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string'
                                    ]);
                            });
                    });
            });
    }

    public function test_get_all_books_search_success(): void
    {
        Book::factory(['title' => 'vaseline'])->create();

        $response = $this->getJson('/api/books?search=vas');

        $response->assertOk()

            ->assertJson(function (AssertableJson $json) {

                $json->hasAll(['message', 'data'])
                    ->whereAllType([
                        'message' => 'string'
                    ])
                    ->has('data', 1, function (AssertableJson $json) {
                        $json->hasAll(['id', 'title', 'author', 'image', 'genre'])
                            ->whereAllType([
                                'id' => 'integer',
                                'title' => 'string',
                                'author' => 'string',
                                'image' => 'string'
                            ])
                            ->has('genre', function (AssertableJson $json) {
                                $json->hasAll(['id', 'name'])
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string'
                                    ]);
                            });
                    });
            });
    }

    public function test_no_books_found(): void
    {
        $response = $this->getJson('api/books/');
        $response->assertStatus(404)
            ->assertJson(function (AssertableJson $json) {
                $json->has('message')
                    ->where(
                        'message',
                        "No books found"
                    );
            });
    }

    public function test_search_no_books_found(): void
    {
        $response = $this->getJson('api/books?search=ilonka');
        $response->assertStatus(404)
            ->assertJson(function (AssertableJson $json) {
                $json->has('message')
                    ->where(
                        'message',
                        "No books found"
                    );
            });
    }

    public function test_get_book_by_id(): void
    {
        $book = Book::factory()->create();
        Review::factory()->count(5)->create(['book_id' => $book->id]);

        $response = $this->getJson('api/books/' . $book->id);

        $response->assertStatus(200)

            ->assertJson(function (AssertableJson $json) {
                $json->hasAll(['message', 'data'])
                    ->whereAllType([
                        'message' => 'string'
                    ])

                    ->has('data', function (AssertableJson $json) {
                        $json->hasAll(['id', 'title', 'author', 'blurb', 'image', 'claimed_by_name', 'page_count', 'year', 'genre', 'isbn10', 'isbn13', 'language'])
                            ->whereAllType([
                                'id' => 'integer',
                                'title' => 'string',
                                'author' => 'string',
                                'blurb' => 'string',
                                'image' => 'string',
                                'claimed_by_name' => 'string',
                                'page_count' => 'integer',
                                'year' => 'integer',
                                'isbn10' => 'string',
                                'isbn13' => 'string',
                                'language' => 'string'
                            ])
                            ->has('genre', function (AssertableJson $json) {
                                $json->hasAll(['id', 'name'])
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string'
                                    ]);
                            })

                            ->has('reviews', 5, function (AssertableJson $json) {
                                $json->hasAll([
                                    'id',
                                    'name',
                                    'rating',
                                    'review',
                                ])
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string',
                                        'rating' => 'integer',
                                        'review' => 'string',
                                    ]);
                            });
                    });
            });
    }

    public function test_book_not_found(): void
    {
        $response = $this->getJson('/api/books/564756445323254');
        $response->assertStatus(404);
        $response->assertJson(function (AssertableJson $json) {
            $json->has('message')
                ->where(
                    'message',
                    'Book with id 564756445323254 not found'
                );
        });
    }

    public function test_book_not_found_claim(): void
    {
        $response = $this->putJson('/api/books/claim/564756445323254', [
            'name' => 'test',
            'email' => 'test@test.com'
        ]);
        $response->assertStatus(404);
        $response->assertJson(function (AssertableJson $json) {
            $json->has('message')
                ->where(
                    'message',
                    'Book 564756445323254 was not found'
                );
        });
    }

    public function test_book_already_claimed(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson("api/books/claim/$book->id", [
            'name' => 'test',
            'email' => 'test@test.com'
        ]);
        $response->assertStatus(400);
        $response->assertJson(function (AssertableJson $json) use ($book) {
            $json->has('message')
                ->where(
                    'message',
                    "Book $book->id is already claimed"
                );
        });
    }

    public function test_book_claim_success(): void
    {
        $book = Book::factory(['claimed_by_name' => null])->create();

        $response = $this->putJson("api/books/claim/$book->id", [
            'name' => 'test',
            'email' => 'test@test.com'
        ]);
        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) use ($book) {
            $json->has('message')
                ->where(
                    'message',
                    "Book $book->id was claimed"
                );
        });
    }

    public function test_book_claim_no_name_no_email(): void
    {
        $response = $this->putJson('api/books/claim/1', [
            'name' => '',
            'email' => ''
        ]);
        $response->assertStatus(422);
        $response->assertInvalid(['name', 'email']);
    }

    public function test_book_claim_invalid_email(): void
    {
        $response = $this->putJson('api/books/claim/1', [
            'name' => 'test',
            'email' => 'test'
        ]);
        $response->assertStatus(422);
        $response->assertInvalid('email');
    }

    public function test_get_books_invalid_genre(): void
    {
        $response = $this->getJson('api/books?genre=thriller');
        $response->assertStatus(422);
        $response->assertInvalid('genre');
    }

    public function test_book_claim_db_success(): void
    {
        $book = Book::factory(['claimed_by_name' => null])->create();

        $this->putJson("api/books/claim/$book->id", [
            'name' => 'test',
            'email' => 'test@test.com'
        ]);

        $this->assertDatabaseHas('books', [
            'claimed_by_name' => 'test',
            'claimed_by_email' => 'test@test.com',
            'claimed' => 1
        ]);
    }

    public function test_book_return_invalid_email()
    {
        $response = $this->putJson('api/books/return/1', ['email' => 'test']);
        $response->assertStatus(422);
        $response->assertInvalid('email');
    }

    public function test_book_not_found_return()
    {
        $response = $this->putJson('/api/books/return/564756445323254', [
            'email' => 'test@test.com'
        ]);
        $response->assertStatus(404);
        $response->assertJson(function (AssertableJson $json) {
            $json->has('message')
                ->where(
                    'message',
                    'Book 564756445323254 was not found'
                );
        });
    }

    public function test_book_not_currently_claimed()
    {
        $book = Book::factory(['claimed_by_name' => null])->create();

        $response = $this->putJson("api/books/return/$book->id", [
            'email' => 'test@test.com'
        ]);

        $response->assertStatus(400);
        $response->assertJson(function (AssertableJson $json) use ($book) {
            $json->has('message')
                ->where(
                    'message',
                    "Book $book->id is not currently claimed"
                );
        });
    }

    public function test_book_incorrect_claimed_email()
    {
        $book = Book::factory(['claimed_by_email' => 'not_test@test.com'])->create();

        $response = $this->putJson("api/books/return/$book->id", [
            'email' => 'test@test.com'
        ]);

        $response->assertStatus(400);
        $response->assertJson(function (AssertableJson $json) use ($book) {
            $json->has('message')
                ->where(
                    'message',
                    "Book $book->id was not returned. test@test.com did not claim this book."
                );
        });
    }

    public function test_book_return_success()
    {
        $book = Book::factory()->create();

        $response = $this->putJson("api/books/return/$book->id", [
            'email' => $book->claimed_by_email
        ]);

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) use ($book) {
            $json->has('message')
                ->where(
                    'message',
                    "Book $book->id was returned"
                );
        });

        $this->assertDatabaseHas('books', [
            'claimed_by_name' => null,
            'claimed_by_email' => null,
            'claimed' => 0
        ]);
    }

    public function test_review_add_no_data(): void
    {
        $response = $this->postJson('/api/books', []);
        $response->assertStatus(422)
            ->assertInvalid(['title', 'author', 'genre_id']);
    }

    public function test_book_add_invalid_data(): void
    {
        $response = $this->postJson('/api/books', [
            'title' => 7,
            'author' => 7,
            'genre_id' => 'hfenfdiefh',
            'blurb' => 7,
            'image' => 'uiwegfuewh',
            'year' => 'heheij'

        ]);
        $response->assertStatus(422)
            ->assertInvalid(['title', 'author', 'genre_id', 'blurb', 'image', 'year']);
    }

    public function test_book_add_success(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/books', [
            'title' => 'book title',
            'author' => 'book person',
            'genre_id' => $genre->id,
            'blurb' => 'fidugfjkfhihd',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/3/33/Fresh_made_bread_05.jpg',
            'year' => 6767,
            'isbn10' => '1565847032',
            'isbn13' => '9781565847033',
            'language' => 'en'
        ]);

        $response->assertStatus(201);
        $response->assertJson(function (AssertableJson $json) {
            $json->has('message')
                ->where(
                    'message',
                    "Book created"
                );
        });

        $this->assertDatabaseHas('books', [
            'title' => 'book title',
            'author' => 'book person',
            'genre_id' => $genre->id,
            'blurb' => 'fidugfjkfhihd',
            'image' => 'https://upload.wikimedia.org/wikipedia/commons/3/33/Fresh_made_bread_05.jpg',
            'year' => 6767,
            'isbn10' => '1565847032',
            'isbn13' => '9781565847033',
            'language' => 'en'
        ]);
    }
};
