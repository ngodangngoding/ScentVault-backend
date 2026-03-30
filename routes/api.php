<?php
use App\Http\Controllers\BrandController;
use Illuminate\Support\Facades\Route;

Route::apiResource('brands', BrandController::class);


?>