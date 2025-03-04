<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    use HasFactory;

    protected $table = 'checkouts';

    protected $fillable = ['book_id', 'user_id', 'status', 'checkout_date', 'due_date'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class); // Links checkout to a customer
    }

}

