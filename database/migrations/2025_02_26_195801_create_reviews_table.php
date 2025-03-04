<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade'); // Links to books table
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Links to users table
            $table->integer('rating')->check('rating >= 1 AND rating <= 5'); // Rating from 1 to 5
            $table->text('review_text')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};

