<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookCopyFactory extends Factory
{
    protected $model = BookCopy::class;

    public function definition()
    {
        $status = $this->faker->randomElement(['available', 'checked_out']);

        // If checked out, set checkout_date, due_date, and user_id
        $date = date('Y-m-d');
        $checkoutDate = $status === 'checked_out' ? date('Y-m-d',strtotime("-1 days"))  : null;
        $dueDate = $checkoutDate ? date('Y-m-d', strtotime($checkoutDate. ' + 5 days')) : null;
        $userId = $status === 'checked_out' ? User::inRandomOrder()->first()->id : null;

        return [
            'book_id' => Book::inRandomOrder()->first()->id,
            'user_id' => $userId,
            'status' => $status,
            'checkout_date' => $checkoutDate,
            'due_date' => $dueDate,
        ];
    }
}
