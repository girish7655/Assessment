<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookAuthorFactory extends Factory
{
    protected $model = \App\Models\BookAuthor::class;

    public function definition()
    {
        return [
            'book_id' => Book::factory(),
            'author_id' => Author::factory(),
        ];
    }
}

