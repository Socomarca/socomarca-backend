<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PriceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('subcategories', SubcategoryController::class)->only(['index']);
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
});

// Rutas públicas (si aplica) — aquí podrías mover las no protegidas
Route::apiResource('brands', BrandController::class)->only(['index']);
Route::apiResource('prices', PriceController::class)->only(['index']);

// Route::prefix('v1')->group(function () {
//     Route::apiResource('categories', CategoryController::class);
//     Route::apiResource('subcategories', SubcategoryController::class);
//     Route::apiResource('brands', BrandController::class);
//     Route::apiResource('products', ProductController::class);
//     Route::apiResource('prices', PriceController::class); 
// });