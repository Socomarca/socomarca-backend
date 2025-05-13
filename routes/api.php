<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('throttle:6,1')->group(function () {
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('password.email');
    //Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
    Route::post('/verify-token', [PasswordResetController::class, 'verifyToken'])->name('password.verify');

    Route::get('/reset-password', [PasswordResetController::class, 'resetPasswordByRut'])->name('password.reset');
    Route::post('/verify-token', [PasswordResetController::class, 'verifyTokenByRut'])->name('password.verify');
});

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    
    // Rutas para administradores
    Route::middleware('role:super-admin|admin')->group(function () {
        // Rutas administrativas
    });
    
    // Rutas para clientes
    Route::middleware('role:cliente')->group(function () {
        // Rutas para clientes
    });
});