<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@scentvault.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'), // Silakan ganti nanti
                'role' => 'admin',
            ]
        );

        // Regular users
        User::factory(10)->create(['role' => 'user']);
    }
}
