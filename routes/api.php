<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Middleware\JWTMiddleware;

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello World!',
    ]);
});

Route::middleware([JWTMiddleware::class, 'api'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->middleware('can:ver usuarios');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->middleware('can:crear usuarios');
        Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])->middleware('can:ver usuarios');
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->middleware('can:editar usuarios');
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->middleware('can:eliminar usuarios');
    });
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login-google', [AuthController::class, 'googleLogin']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Route::middleware(JWTMiddleware::class)->group(function () {
//     Route::prefix('auth')->group(function () {
//         Route::get('/me', [AuthController::class, 'me']);
//         Route::get('/refresh', [AuthController::class, 'refresh']);
//         Route::get('/logout', [AuthController::class, 'logout']);
//     });
// });

Route::middleware(JWTMiddleware::class, 'api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });
});
