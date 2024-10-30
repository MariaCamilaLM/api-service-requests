<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'test@example.com',
            
            'profile' => 'admin'
        ]);

        User::factory()->create([
            'name' => 'Client User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Engineer User',
            'email' => 'test@example.com',
        ]);
    }
}
