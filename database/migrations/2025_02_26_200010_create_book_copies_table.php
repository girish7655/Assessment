<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade'); // Links to books table
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Links to books table
            $table->enum('status', ['available', 'checked_out','returned'])->default('available'); // Book availability
            $table->date('checkout_date')->nullable(); // Date when checked out
            $table->date('due_date')->nullable(); // Due date (5 days after checkout)
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
