<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomePageResource;
use App\Http\Resources\HomeScentLogResource;
use App\Models\ScentLog;
use App\Services\PerfumeRecommendationService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('User - Pages', 'Endpoint data halaman frontend yang membutuhkan login user.', 12)]
class HomePageController extends Controller
{
    public function __construct(
        protected PerfumeRecommendationService $recommendationService
    ) {}

    public function show(Request $request)
    {
        $user = $request->user()->loadCount(['perfumes', 'scentLogs']);

        $recommendationPayload = $this->recommendationService->getCurrent($user);
        $recommendedPerfume = collect($recommendationPayload['data'])->first();

        $scentLogs = ScentLog::query()
            ->with([
                'perfume:id,name',
                'occasion:id,name',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Home page data fetched successfully.',
            'data' => [
                'summary' => [
                    'total_perfumes' => $user->perfumes_count,
                    'total_scent_logs' => $user->scent_logs_count,
                ],
                'today_recommendation' => [
                    'status_code' => $recommendationPayload['status'],
                    'message' => $recommendationPayload['message'],
                    'context' => $recommendationPayload['current_context'],
                    'perfume' => $recommendedPerfume ? [
                        'perfume_id' => $recommendedPerfume['perfume_id'],
                        'brand' => $recommendedPerfume['brand'],
                        'name' => $recommendedPerfume['name'],
                        'notes' => $recommendedPerfume['notes'],
                        'description' => $recommendedPerfume['description'],
                    ] : null,
                ],
                'scent_logs' => HomeScentLogResource::collection($scentLogs),
            ],
        ]);
    }
}
