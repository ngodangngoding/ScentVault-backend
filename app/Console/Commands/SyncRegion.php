<?php

namespace App\Console\Commands;

use App\Models\Region;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

#[Signature('app:sync-region')]
#[Description('Sync master data region from API')]
class SyncRegion extends Command
{
    public function handle(): int
    {
        $this->info('Start sync region...');

        $provinces = $this->getData('https://wilayah.id/api/provinces.json');

        if (empty($provinces)) {
            $this->error('Failed get Provinces Data');
            return self::FAILURE;
        }

        foreach ($provinces as $province) {
            $this->saveRegion(
                code: $province['code'],
                name: $province['name'],
                level: 1,
                parentCode: null
            );

            $this->line("Provinsi: {$province['name']}");

            $regencies = $this->getData("https://wilayah.id/api/regencies/{$province['code']}.json");
            foreach ($regencies as $regency) {
                $this->saveRegion(
                    code: $regency['code'],
                    name: $regency['name'],
                    level: 2,
                    parentCode: $province['code']
                );

                $districts = $this->getData("https://wilayah.id/api/districts/{$regency['code']}.json");
                foreach ($districts as $district) {
                    $this->saveRegion(
                        code: $district['code'],
                        name: $district['name'],
                        level: 3,
                        parentCode: $regency['code'],
                    );

                    $villages = $this->getData("https://wilayah.id/api/villages/{$district['code']}.json");
                    foreach ($villages as $village) {
                        $this->saveRegion(
                            code: $village['code'],
                            name: $village['name'],
                            level: 4,
                            parentCode: $district['code']
                        );
                    }
                }
            }
        }

        $this->info('Sync region Finished');
        return self::SUCCESS;
    }

    protected function getData($url): array
    {
        try {
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->get($url);

            if (!$response->successful()) {
                $this->error("Response not successful for {$url}. Status: " . $response->status());
                return [];
            }

            return $response->json('data') ?? [];
        } catch (\Throwable $e) {
            $this->error("Request failed: {$url}");
            $this->error("Error message: " . $e->getMessage());
            return [];
        }
    }

    protected function saveRegion(string $code, string $name, int $level, ?string $parentCode): void
    {
        Region::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'level' => $level,
                'parent_code' => $parentCode,
                'normalized_name' => $this->normalize($name),
            ]
        );
    }

    protected function normalize(string $text): string
    {
        $text = Str::lower($text);
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        $replace = [
            'provinsi ' => '',
            'kabupaten ' => '',
            'kota ' => '',
            'kecamatan ' => '',
            'desa ' => '',
            'kelurahan ' => '',
        ];

        return trim(str_replace(array_keys($replace), array_values($replace), $text));
    }
}
