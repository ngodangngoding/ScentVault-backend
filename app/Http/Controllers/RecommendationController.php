<?php

namespace App\Http\Controllers;

use App\Models\RuleConfig;
use App\Services\BmkgWeatherService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RuntimeException;

class RecommendationController extends Controller
{
    public function __construct(
        protected BmkgWeatherService $bmkgWeatherService
    ) {}

    public function current(Request $request)
    {
        $user = $request->user()->load([
            'region',
            'perfumes.brand',
            'perfumes.category',
            'perfumes.perfumeNote',
            'perfumes.suitability',
        ]);

        if (!$user->region_code) {
            return response()->json([
                'message' => 'User belum memiliki region_code.',
            ], 422);
        }

        if ($user->perfumes->isEmpty()) {
            return response()->json([
                'message' => 'User belum memiliki perfume.',
                'current_context' => null,
                'data' => [],
            ], 200);
        }

        try {
            $weather = $this->bmkgWeatherService->getNearestForecast($user->region_code);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
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
            ->filter(fn ($perfume) => $perfume->suitability !== null)
            ->map(function ($perfume) use ($temperatureRules, $timeRules, $weather, $forecastHour) {
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

                // Tie breaker kecil dari rating user
                $score += $starRating / 10;

                return [
                    'perfume_id' => $perfume->id,
                    'name' => $perfume->name,
                    'brand' => $perfume->brand?->name,
                    'category' => $perfume->category?->name,
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

        return response()->json([
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
        ]);
    }

    protected function valueInsideRule(float $min, float $max, float $value): bool
    {
        if ($min <= $max) {
            return $value >= $min && $value < $max;
        }

        // Untuk range yang melewati tengah malam, mis. malam 18-5
        return $value >= $min || $value < $max;
    }
}
