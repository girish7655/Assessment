<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Category;
use App\Models\BookAuthor;
use App\Models\BookCopy;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create authors
        Author::factory(10)->create();

        // Create publishers
        Publisher::factory(5)->create();

        // Create categories
        Category::factory(5)->create();

        // Create books
        Book::factory(10)->create(); 

        // Attach authors to books (Each book can have multiple authors)
        Book::all()->each(function ($book) {
            $authors = Author::inRandomOrder()->take(rand(1, 2))->pluck('id'); // Assign 1-2 authors per book
                $book->authors()->attach($authors);
        });
    }
}

