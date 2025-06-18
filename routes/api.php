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
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
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
    Route::get('/profile', [UserController::class, 'profile']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/search', [UserController::class, 'search'])->middleware('permission:manage-users');
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/search', [UserController::class, 'searchUsers'])->middleware('role:admin|superadmin');

    Route::middleware(['role:admin|superadmin'])->group(function () {
        Route::get('/roles/users', [RoleController::class, 'rolesWithUsers']);
        Route::get('/roles/{user}', [RoleController::class, 'userRoles']);
    });

    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}', [AddressController::class, 'show'])->name('addresses.show');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    Route::get('/categories', [CategoryController::class,'index'])->name('categories.index');
    Route::post('/categories/search', [CategoryController::class, 'search'])->name('categories.search');
    Route::get('/categories/{id}', [CategoryController::class,'show'])->name('categories.show');
    Route::get('/subcategories', [SubcategoryController::class,'index'])->name('subcategories.index');
    Route::get('/subcategories/{id}', [SubcategoryController::class,'show'])->name('subcategories.show');

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::post('/products/search', [ProductController::class, 'search'])->name('products.search');

    Route::get('/favorites-list', [FavoriteListController::class, 'index'])->name('favorites-list.index');
    Route::post('/favorites-list', [FavoriteListController::class, 'store'])->name('favorites-list.store');
    Route::get('/favorites-list/{id}', [FavoriteListController::class, 'show'])->name('favorites-list.show');
    Route::put('/favorites-list/{id}', [FavoriteListController::class, 'update'])->name('favorites-list.update');
    Route::delete('/favorites-list/{id}', [FavoriteListController::class, 'destroy'])->name('favorites-list.destroy');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');

    Route::get('/cart', [CartController::class, 'index']);
    Route::delete('/cart', [CartItemController::class, 'emptyCart'])->name('cart.empty');
    Route::post('/cart/items', [CartItemController::class, 'store']);
    Route::delete('/cart/items', [CartItemController::class, 'destroy']);

    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update']);

    Route::apiResource('brands', BrandController::class)->only(['index']);

    Route::apiResource('prices', PriceController::class)->only(['index']);

    // Rutas de orden
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/pay', [OrderController::class, 'payOrder']);


    Route::middleware(['auth:sanctum', 'permission:see-all-reports'])->group(function () {
        Route::post('/orders/reports', [ReportController::class, 'report']);

        Route::post('/orders/reports/top-product-list', [ReportController::class, 'productsSalesList']);
        Route::post('/orders/reports/transactions-list', [ReportController::class, 'transactionsList']);
        Route::post('/orders/reports/clients-list', [ReportController::class, 'clientsList']);
        Route::post('/orders/reports/failed-transactions-list', [ReportController::class, 'failedTransactionsList']);
    });

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
