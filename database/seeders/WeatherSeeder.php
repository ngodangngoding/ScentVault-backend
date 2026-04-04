<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeatherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $weathers = ['Sunny', 'Cloudy', 'Rain', 'Thunder'];

        foreach ($weathers as $weather) {
            DB::table('weather')->updateOrInsert(
                ['name' => $weather],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
