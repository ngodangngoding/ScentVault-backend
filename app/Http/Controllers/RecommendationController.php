<?php

namespace App\Http\Controllers;

use App\Services\PerfumeRecommendationService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('User - Recommendations', 'Endpoint rekomendasi parfum untuk user yang sudah login.', 7)]
class RecommendationController extends Controller
{
    public function __construct(
        protected PerfumeRecommendationService $recommendationService
    ) {}

    /**
     * Get current perfume recommendations based on user region, weather, and time context.
     *
     * @return array{message: string, current_context: array{region_code: string|null, region_name: string|null, forecast_time: string, temperature: float, weather_desc: string, temperature_label: string, time_label: string}|null, data: array<int, array{perfume_id: int, name: string, brand: string|null, category: string|null, description: string|null, notes: array{top: string|null, middle: string|null, base: string|null}, score: float, star_rating: int, match: array{temperature: bool, time: bool}, suitability: array{ideal_temperature: string, ideal_time: string, ideal_environment: string}}>}
     */
    public function current(Request $request)
    {
        $payload = $this->recommendationService->getCurrent($request->user());

        return response()->json([
            'message' => $payload['message'],
            'current_context' => $payload['current_context'],
            'data' => $payload['data'],
        ], $payload['status']);
    }
}
