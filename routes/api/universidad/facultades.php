<?php

use App\Http\Controllers\Universidad\FacultadController;
use App\Http\Middleware\AuthzMiddleware;
use App\Http\Middleware\JWTMiddleware;
use App\Models\Universidad\Facultad;
use Illuminate\Support\Facades\Route;

Route::middleware([JWTMiddleware::class, 'api'])->group(
    function (): void {
        Route::prefix('v1')->group(function (): void {
            Route::get('/facultades', [FacultadController::class, 'indexAll'])->middleware('can:ver facultades');
            Route::get('/facultades/paginated', [FacultadController::class, 'index'])->middleware('can:ver facultades');
            Route::post('/facultades', [FacultadController::class, 'store'])->middleware('can:manage facultades');
            Route::get('/facultades/{id}', [FacultadController::class, 'show'])->middleware([AuthzMiddleware::class . ':ver facultades,' . Facultad::class]);
            Route::put('/facultades/{id}', [FacultadController::class, 'update'])->middleware([AuthzMiddleware::class . ':manage facultades,' . Facultad::class]);
            Route::delete('/facultades/{id}', [FacultadController::class, 'destroy'])->middleware([AuthzMiddleware::class . ':manage facultades,' . Facultad::class]);
            Route::get('/facultades/nombre/{nombre}', [FacultadController::class, 'showByName'])->middleware('can:ver facultades');
        });
    }
);