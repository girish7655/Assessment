<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Category;
use Faker\Factory as Faker;

class CreateBook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'book:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new book every 5 minutes automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $faker = Faker::create();

        $author = Author::create(['name' => 'Author Cronjob '.$faker->name]);
        $category = Category::create(['name' => 'Category Cronjob '.$faker->name]);
        $publisher = Publisher::create(['name' => 'Publisher Cronjob '.$faker->name]);

        // Create a new book
        Book::create([
            'title' => 'Book CronJob '.$faker->sentence(3),
            'description' => 'Description CronJob '.$faker->paragraph(),
            'cover_image' => null,
            'publication_date' => now(),
            'isbn' => $faker->unique()->isbn13(),
            'page_count' => $faker->numberBetween(100, 1000),
            'author_id' => $author->id,
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'published_at' => now(),
        ]);

        $this->info('A new book has been created. ');
    }

}
