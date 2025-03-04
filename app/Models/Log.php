<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    // Specify the table name (if different from the plural form of the model name)
    protected $table = 'logs'; 

    // Define the fields that are mass-assignable
    protected $fillable = [
        'user_id',          // The ID of the user who performed the action
        'action',           // The type of action (e.g., login, logout)
        'description',      // A description of the event (e.g., success or failure)
    ];

    // Define any relationships, if necessary
    // For example, if you want to define a relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
