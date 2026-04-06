<?php

namespace Database\Seeders;

use App\Models\RuleConfig;
use Illuminate\Database\Seeder;

class RuleConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            // Temperature rules (°C)
            ['type' => 'temperature', 'label' => 'dingin', 'min_value' => 0, 'max_value' => 20],
            ['type' => 'temperature', 'label' => 'normal', 'min_value' => 20, 'max_value' => 30],
            ['type' => 'temperature', 'label' => 'panas', 'min_value' => 30, 'max_value' => 50],

            // Time rules (jam, format 24h)
            ['type' => 'time', 'label' => 'pagi', 'min_value' => 5, 'max_value' => 11],
            ['type' => 'time', 'label' => 'siang', 'min_value' => 11, 'max_value' => 15],
            ['type' => 'time', 'label' => 'sore', 'min_value' => 15, 'max_value' => 18],
            ['type' => 'time', 'label' => 'malam', 'min_value' => 18, 'max_value' => 5],
        ];

        foreach ($rules as $rule) {
            RuleConfig::updateOrCreate(
                ['type' => $rule['type'], 'label' => $rule['label']],
                $rule
            );
        }
    }
}
