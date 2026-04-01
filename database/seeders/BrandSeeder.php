<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('brands')->insert([
            [
                'name' => 'Mykonos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'HMNS',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
