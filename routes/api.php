<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('subcategories', SubcategoryController::class)->only(['index']);
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
});

Route::apiResource('brands', BrandController::class)->only(['index']);
Route::apiResource('prices', PriceController::class)->only(['index']);

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
