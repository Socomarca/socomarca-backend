<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FavoriteListController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WebpayController;





Route::prefix('auth')->group(function () {
    Route::post('/token', [AuthController::class, 'login'])->name('auth.token.store');
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/restore', [PasswordResetController::class, 'forgotPassword'])->name('auth.password.restore');
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/check-token', function () {
            return response()->json(['valid' => true]);
        })->name('auth.check.token');
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

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::post('/products/search', [ProductController::class, 'search'])->name('products.search');

    Route::get('/favorites-list', [FavoriteListController::class, 'index']);
    Route::post('/favorites-list', [FavoriteListController::class, 'store']);
    Route::get('/favorites-list/{id}', [FavoriteListController::class, 'show']);
    Route::put('/favorites-list/{id}', [FavoriteListController::class, 'update']);
    Route::delete('/favorites-list/{id}', [FavoriteListController::class, 'destroy']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartItemController::class, 'store']);
    Route::delete('/cart/items', [CartItemController::class, 'destroy']);

    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update']);

    Route::apiResource('brands', BrandController::class)->only(['index']);

    Route::apiResource('prices', PriceController::class)->only(['index']);

    // Rutas de orden
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/create-from-cart', [OrderController::class, 'createFromCart']);

    // Rutas de Webpay
    Route::post('/orders/pay', [OrderController::class, 'payOrder']);
    
    Route::post('/orders/serach', [OrderController::class, 'searchOrders'])->name('orders.search');
    Route::get('/orders/{id}', [OrderController::class, 'show']);

});

//Se sacan de la autenticacion porque es confirmacion de pago.
//Front recibe el token y lo envia a /webpay/return  (La ruta se establece en el webpayService: linea 59)
///webpay/return valida la token y entrega al front el estado del pago
Route::get('/webpay/return', [WebpayController::class, 'return'])->name('webpay.return');
Route::get('/webpay/status', [WebpayController::class, 'status']);
Route::post('/webpay/refund', [WebpayController::class, 'refund']);

// Ruta catch-all al final
Route::any('{url}', function() {
    return response()->json(['message' => 'Method Not Allowed.'], 405);
})->where('url', '.*');
