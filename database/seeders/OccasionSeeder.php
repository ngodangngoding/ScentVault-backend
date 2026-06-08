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
                'name' => 'Aktivitas Harian',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kerja',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Acara Santai',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kuliah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Olahraga',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Aktivitas Luar Ruangan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Acara Formal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Acara Malam',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
