<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'cover_image' => null,
            'publication_date' => $this->faker->date(),
            'isbn' => $this->faker->unique()->isbn13(),
            'page_count' => $this->faker->numberBetween(100, 600),
            'author_id' => Author::factory(), // Generates an author
            'publisher_id' => Publisher::factory(), // Generates a publisher
            'category_id' => Category::factory(), // Generates a category
        ];
    }
}

