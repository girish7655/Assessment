<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // One Role can have many Users
    public function users()
    {
        return $this->hasMany(User::class, 'role');
    }
}

