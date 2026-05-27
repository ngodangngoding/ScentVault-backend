<?php

namespace App\Services;

use App\Models\Perfume;
use App\Models\RuleConfig;
use App\Models\User;
use Carbon\Carbon;
use RuntimeException;

class PerfumeRecommendationService
{
    protected BmkgWeatherService $bmkgWeatherService;

    public function __construct(BmkgWeatherService $bmkgWeatherService)
    {
        $this->bmkgWeatherService = $bmkgWeatherService;
    }

    public function getCurrent(User $user): array
    {
        $user->loadMissing([
            'region',
            'perfumes.brand',
            'perfumes.category',
            'perfumes.perfumeNote',
            'perfumes.suitability',
        ]);

        // Validation user region
        if (!$user->region_code) {
            return [
                'status' => 422,
                'message' => 'User belum memiliki region_code.',
                'current_context' => null,
                'data' => [],
            ];
        }

        // Validation user perfume
        if ($user->perfumes->isEmpty()) {
            return [
                'status' => 200,
                'message' => 'User belum memiliki perfume.',
                'current_context' => null,
                'data' => [],
            ];
        }

        // get BMKG forecast
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

        $forecastHour = (float) Carbon::parse($weather['forecast_time'], 'Asia/Jakarta')->format('G');

        $temperatureRules = RuleConfig::query()
            ->where('type', 'temperature')
            ->get()
            ->keyBy('label');

        $timeRules = RuleConfig::query()
            ->where('type', 'time')
            ->get()
            ->keyBy('label');

        $currentTemperatureLabel = RuleConfig::getLabelFor('temperature', (float) $weather['temperature']);
        $currentTimeLabel = RuleConfig::getLabelFor('time', $forecastHour);

        $recommendations = [];

        foreach ($user->perfumes as $perfume) {

            if ($perfume->suitability === null) {
                continue;
            }

            $suitability = $perfume->suitability;
            $starRating = (int) ($perfume->pivot->star_rating ?? 0);

            // find ideal rule
            $temperatureRule = $temperatureRules->get($suitability->ideal_temperature);
            $timeRule = $timeRules->get($suitability->ideal_time);

            // check temperature match
            if ($temperatureRule) {
                $temperatureMatch = $this->valueInsideRule(
                    $temperatureRule->min_value,
                    $temperatureRule->max_value,
                    (float) $weather['temperature']
                );
            } else {
                $temperatureMatch = false;
            }

            // check time rule
            if ($timeRule) {
                $timeMatch = $this->valueInsideRule(
                    $timeRule->min_value,
                    $timeRule->max_value,
                    $forecastHour
                );
            } else {
                $timeMatch = false;
            }

            // Calculate score
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

            $recommendations[] = [
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
        }

        // Sort from high to low
        $recommendations = collect($recommendations)->sortByDesc('score')->values()->all();

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
        $topNote = $perfume->perfumeNote->firstWhere('pivot.type', 'top');
        $middleNote = $perfume->perfumeNote->firstWhere('pivot.type', 'middle');
        $baseNote = $perfume->perfumeNote->firstWhere('pivot.type', 'base');

        return [
            'top' => data_get($topNote, 'name'),
            'middle' => data_get($middleNote, 'name'),
            'base' => data_get($baseNote, 'name'),
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
