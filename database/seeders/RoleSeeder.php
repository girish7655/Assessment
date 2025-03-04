<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::insert([
            ['name' => 'customer','created_at' => now(), 'updated_at' => now()],
            ['name' => 'librarian', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
