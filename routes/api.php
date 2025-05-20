<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\UserController;
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

Route::prefix('auth')->group(function () {
    Route::post('/token', [AuthController::class, 'login'])->name('auth.token.store');
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/restore', [PasswordResetController::class, 'forgotPassword'])->name('auth.password.restore');
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/token', [AuthController::class, 'destroy'])->name('auth.token.destroy');
        Route::prefix('/password')->group(function () {
            Route::put('', [PasswordResetController::class, 'changePassword'])->name('password.update');
            Route::get('/status', [PasswordResetController::class, 'checkPasswordStatus'])->name('password.status');
        });
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

    Route::get('/categories', [CategoryController::class,'index'])->name('categories.index');
    Route::get('/categories/{id}', [CategoryController::class,'show'])->name('categories.show');
    Route::get('/subcategories', [SubcategoryController::class,'index'])->name('subcategories.index');
    Route::get('/subcategories/{id}', [SubcategoryController::class,'show'])->name('subcategories.show');

    Route::get('/products', [ProductController::class,'index'])->name('products.index');
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('/products/{id}', [ProductController::class,'show'])->name('products.show');
    Route::get('/products/category/{categoryId}', [ProductController::class, 'byCategory'])->name('products.byCategory');
    
});

Route::apiResource('brands', BrandController::class)->only(['index']);

Route::apiResource('prices', PriceController::class)->only(['index']);
