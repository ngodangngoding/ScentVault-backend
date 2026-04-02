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
                'name' => 'Bergamot',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Bigarade',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Bitter Orange',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Apple',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Acacia',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

    }
}
