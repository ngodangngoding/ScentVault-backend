<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('notes')->insert([
            [
                'name' => 'Top',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Middle',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Base',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

    }
}
