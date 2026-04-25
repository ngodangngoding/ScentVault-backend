<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IntegrationStatusController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OccasionController;
use App\Http\Controllers\Pages\HomePageController;
use App\Http\Controllers\Pages\PerfumeCollectionPageController;
use App\Http\Controllers\PerfumeController;
use App\Http\Controllers\PerfumeSuitabilityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ScentLogController;
use App\Http\Controllers\RuleConfigController;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pages/home', [HomePageController::class, 'show']);
    Route::get('/pages/perfume-collection', [PerfumeCollectionPageController::class, 'show']);

    Route::apiResource('perfumes', PerfumeController::class);
    Route::put('/perfumes/{perfume}/suitability', [PerfumeSuitabilityController::class, 'update']);
    Route::get('perfumes/{perfume}/suitability', [PerfumeSuitabilityController::class, 'show']);
    Route::get('/me', [ProfileController::class, 'show']);
    Route::patch('/me', [ProfileController::class, 'update']);
    Route::patch('/me/password', [ProfileController::class, 'updatePassword']);
    Route::patch('/me/region', [ProfileController::class, 'updateRegion']);
    Route::post('/me/avatar', [ProfileController::class, 'updateAvatar']);
    Route::apiResource('scentLog', ScentLogController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/recommendations/current', [RecommendationController::class, 'current']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('rule-configs', RuleConfigController::class);
    Route::get('/integration-status', [IntegrationStatusController::class, 'index']);

    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::patch('/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole']);
});

Route::prefix('region')->group(function () {
    Route::get('/provinces', [RegionController::class, 'provinces']);
    Route::get('/regencies', [RegionController::class, 'regencies']);
    Route::get('/districts', [RegionController::class, 'districts']);
    Route::get('/villages', [RegionController::class, 'villages']);
});
