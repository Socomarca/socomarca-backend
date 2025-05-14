<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\SubcategoryController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PriceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
//     Route::apiResource('categories', CategoryController::class);
//     Route::apiResource('subcategories', SubcategoryController::class);
//     Route::apiResource('brands', BrandController::class);
//     Route::apiResource('products', ProductController::class);
//     Route::apiResource('prices', PriceController::class);
// });

Route::prefix('v1')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('subcategories', SubcategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('prices', PriceController::class); // Asegúrate de que "prices" esté bien escrito (sin doble "r")
});