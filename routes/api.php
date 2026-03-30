<?php
use App\Http\Controllers\BrandController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::apiResource('brands', BrandController::class);
Route::apiResource('notes', NoteController::class);

?>