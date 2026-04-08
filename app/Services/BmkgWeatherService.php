<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BmkgWeatherService
{
    public function getNearestForecast(string $adm4, ?Carbon $now = null): array
    {
        $now = ($now ?: now('Asia/Jakarta'))->copy()->timezone('Asia/Jakarta');

        $payload = Cache::remember(
            "bmkg.forecast.{$adm4}",
            now()->addMinutes((int) config('services.bmkg.cache_minutes', 10)),
            fn () => $this->fetchForecast($adm4),
        );

        $slots = $this->extractSlots($payload);

        if ($slots->isEmpty()) {
            throw new RuntimeException('Forecast slot tidak ditemukan dari BMKG.');
        }

        $nearest = $slots
            ->map(function (array $slot) use ($now) {
                $forecastAt = Carbon::parse($slot['local_datetime'], 'Asia/Jakarta');

                $slot['forecast_at'] = $forecastAt;
                $slot['distance_seconds'] = abs($forecastAt->timestamp - $now->timestamp);
                $slot['prefer_future'] = $forecastAt->lt($now) ? 1 : 0;

                return $slot;
            })
            ->sortBy([
                ['distance_seconds', 'asc'],
                ['prefer_future', 'asc'],
            ])
            ->first();

        return [
            'adm4' => $adm4,
            'requested_at' => $now->toDateTimeString(),
            'forecast_time' => $nearest['forecast_at']->toDateTimeString(),
            'temperature' => (float) $nearest['t'],
            'weather_desc' => $nearest['weather_desc'] ?? null,
            'humidity' => isset($nearest['hu']) ? (float) $nearest['hu'] : null,
            'raw' => $nearest,
        ];
    }

    protected function fetchForecast(string $adm4): array
    {
        $response = Http::baseUrl(config('services.bmkg.base_url'))
            ->timeout((int) config('services.bmkg.timeout', 10))
            ->withoutVerifying()
            ->acceptJson()
            ->get('/prakiraan-cuaca', [
                'adm4' => $adm4,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Gagal mengambil data BMKG. Status: ' . $response->status()
            );
        }

        return $response->json();
    }

    protected function extractSlots(array $payload): Collection
    {
        $groups = data_get($payload, 'data.0.cuaca', []);

        return collect($groups)
            ->flatMap(fn ($group) => is_array($group) ? $group : [])
            ->filter(fn ($item) => isset($item['local_datetime'], $item['t']))
            ->values();
    }
}
