<?php

namespace App\Http\Controllers;

use App\Services\PerfumeRecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(
        protected PerfumeRecommendationService $recommendationService
    ) {}

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
