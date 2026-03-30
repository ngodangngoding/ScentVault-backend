<?php
use App\Http\Controllers\BrandController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OccasionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('brands', BrandController::class);
Route::apiResource('notes', NoteController::class);
Route::apiResource('occasions', OccasionController::class);

?>