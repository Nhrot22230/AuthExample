<?php

use App\Http\Controllers\Authorization\AuthController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login-google', [AuthController::class, 'googleLogin']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware([JWTMiddleware::class, 'api'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/me', [AuthController::class, 'me']);
    });
});

Route::middleware([JWTMiddleware::class, 'api'])->group(
    function () {
        Route::prefix('v1')->group(function () {
            Route::get('/mis-unidades', [AuthController::class, 'obtenerMisUnidades']);
        });
    }
);