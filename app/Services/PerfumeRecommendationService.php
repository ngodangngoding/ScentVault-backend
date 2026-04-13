<?php

namespace App\Services;

use App\Models\Perfume;
use App\Models\RuleConfig;
use App\Models\User;
use Carbon\Carbon;
use RuntimeException;

class PerfumeRecommendationService
{
    public function __construct(
        protected BmkgWeatherService $bmkgWeatherService
    ) {}

    public function getCurrent(User $user): array
    {
        $user->loadMissing([
            'region',
            'perfumes.brand',
            'perfumes.category',
            'perfumes.perfumeNote',
            'perfumes.suitability',
        ]);

        if (!$user->region_code) {
            return [
                'status' => 422,
                'message' => 'User belum memiliki region_code.',
                'current_context' => null,
                'data' => [],
            ];
        }

        if ($user->perfumes->isEmpty()) {
            return [
                'status' => 200,
                'message' => 'User belum memiliki perfume.',
                'current_context' => null,
                'data' => [],
            ];
        }

        try {
            $weather = $this->bmkgWeatherService->getNearestForecast($user->region_code);
        } catch (RuntimeException $e) {
            return [
                'status' => 502,
                'message' => $e->getMessage(),
                'current_context' => null,
                'data' => [],
            ];
        }

        $forecastHour = (float) Carbon::parse(
            $weather['forecast_time'],
            'Asia/Jakarta'
        )->format('G');

        $temperatureRules = RuleConfig::query()
            ->where('type', 'temperature')
            ->get()
            ->keyBy('label');

        $timeRules = RuleConfig::query()
            ->where('type', 'time')
            ->get()
            ->keyBy('label');

        $currentTemperatureLabel = RuleConfig::getLabelFor(
            'temperature',
            (float) $weather['temperature']
        );

        $currentTimeLabel = RuleConfig::getLabelFor(
            'time',
            $forecastHour
        );

        $recommendations = $user->perfumes
            ->filter(fn (Perfume $perfume) => $perfume->suitability !== null)
            ->map(function (Perfume $perfume) use ($temperatureRules, $timeRules, $weather, $forecastHour) {
                $suitability = $perfume->suitability;
                $starRating = (int) ($perfume->pivot->star_rating ?? 0);

                $temperatureRule = $temperatureRules->get($suitability->ideal_temperature);
                $timeRule = $timeRules->get($suitability->ideal_time);

                $temperatureMatch = $temperatureRule
                    ? $this->valueInsideRule($temperatureRule->min_value, $temperatureRule->max_value, (float) $weather['temperature'])
                    : false;

                $timeMatch = $timeRule
                    ? $this->valueInsideRule($timeRule->min_value, $timeRule->max_value, $forecastHour)
                    : false;

                $score = 0;

                if ($temperatureMatch) {
                    $score += 2;
                }

                if ($timeMatch) {
                    $score += 2;
                }

                if ($temperatureMatch && $timeMatch) {
                    $score += 1;
                }

                $score += $starRating / 10;

                return [
                    'perfume_id' => $perfume->id,
                    'name' => $perfume->name,
                    'brand' => $perfume->brand?->name,
                    'category' => $perfume->category?->name,
                    'description' => $perfume->description,
                    'notes' => $this->mapNotes($perfume),
                    'score' => round($score, 2),
                    'star_rating' => $starRating,
                    'match' => [
                        'temperature' => $temperatureMatch,
                        'time' => $timeMatch,
                    ],
                    'suitability' => [
                        'ideal_temperature' => $suitability->ideal_temperature,
                        'ideal_time' => $suitability->ideal_time,
                        'ideal_environment' => $suitability->ideal_environment,
                    ],
                ];
            })
            ->sortByDesc('score')
            ->values();

        return [
            'status' => 200,
            'message' => 'Recommendation generated successfully.',
            'current_context' => [
                'region_code' => $user->region_code,
                'region_name' => $user->region?->name,
                'forecast_time' => $weather['forecast_time'],
                'temperature' => $weather['temperature'],
                'weather_desc' => $weather['weather_desc'],
                'temperature_label' => $currentTemperatureLabel,
                'time_label' => $currentTimeLabel,
            ],
            'data' => $recommendations,
        ];
    }

    protected function mapNotes(Perfume $perfume): array
    {
        return [
            'top' => data_get($perfume->perfumeNote->firstWhere('pivot.type', 'top'), 'name'),
            'middle' => data_get($perfume->perfumeNote->firstWhere('pivot.type', 'middle'), 'name'),
            'base' => data_get($perfume->perfumeNote->firstWhere('pivot.type', 'base'), 'name'),
        ];
    }

    protected function valueInsideRule(float $min, float $max, float $value): bool
    {
        if ($min <= $max) {
            return $value >= $min && $value < $max;
        }

        return $value >= $min || $value < $max;
    }
}
