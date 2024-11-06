<?php

use App\Http\Controllers\Tramites\TemaDeTesisController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JWTMiddleware::class, 'api'])->group(
    function (): void {
        Route::prefix('v1')->group(function (): void {
            Route::get('/temas-de-tesis', [TemaDeTesisController::class, 'indexPaginated'])->middleware('can:ver temas de tesis');
            Route::get('/temas-de-tesis/{id}', [TemaDeTesisController::class, 'show'])->middleware('can:ver temas de tesis');
            Route::put('/temas-de-tesis/{id}', [TemaDeTesisController::class, 'update'])->middleware('can:manage temas de tesis');
        });
    }
);