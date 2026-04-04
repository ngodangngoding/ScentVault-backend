<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OccasionController;
use App\Http\Controllers\PerfumeController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ScentLogController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::apiResource('brands', BrandController::class);
Route::apiResource('notes', NoteController::class);
Route::apiResource('occasions', OccasionController::class);
Route::apiResource('weather', WeatherController::class);
Route::apiResource('scentLog', ScentLogController::class);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('perfumes', PerfumeController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('region')->group(function () {
    Route::get('/provinces', [RegionController::class, 'provinces']);
    Route::get('/regencies', [RegionController::class, 'regencies']);
    Route::get('/districts', [RegionController::class, 'districts']);
    Route::get('/villages', [RegionController::class, 'villages']);
});