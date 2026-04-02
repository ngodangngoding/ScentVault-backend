<?php
use App\Http\Controllers\BrandController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OccasionController;
use App\Http\Controllers\PerfumeController;
use App\Http\Controllers\ScentLogController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::apiResource('brands', BrandController::class);
Route::apiResource('notes', NoteController::class);
Route::apiResource('occasions', OccasionController::class);
Route::apiResource('weather', WeatherController::class);
Route::apiResource('perfumes', PerfumeController::class);
Route::apiResource('scentLog', ScentLogController::class);

?>