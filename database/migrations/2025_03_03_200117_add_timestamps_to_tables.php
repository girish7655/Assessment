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
        Schema::table('authors', function (Blueprint $table) {
            $table->integer('created_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->integer('created_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });

        Schema::table('publishers', function (Blueprint $table) {
            $table->integer('created_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });

        Schema::table('books', function (Blueprint $table) {
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
