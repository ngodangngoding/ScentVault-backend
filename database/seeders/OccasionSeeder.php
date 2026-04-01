<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OccasionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('occasions')->insert([
            [
                'name' => 'Work',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Casual',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
