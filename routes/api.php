<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\SubcategoryController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PriceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;

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
    Route::post('/auth/token', [AuthController::class, 'login'])->name('login');
});


Route::middleware('throttle:6,1')->group(function () {
    Route::post('/auth/restore', [PasswordResetController::class, 'forgotPassword'])->name('password.email');
    Route::post('/verify-token', [PasswordResetController::class, 'verifyToken'])->name('password.verify');
});

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/auth/token', [AuthController::class, 'destroy'])->name('destroy');
    Route::post('/change-password', [PasswordResetController::class, 'changePassword'])->name('password.change');
    Route::get('/password-status', [PasswordResetController::class, 'checkPasswordStatus'])->name('password.status');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
});
