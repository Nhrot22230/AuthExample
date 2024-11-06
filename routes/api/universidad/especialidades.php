<?php

use App\Http\Controllers\Universidad\EspecialidadController;
use App\Http\Middleware\AuthzMiddleware;
use App\Http\Middleware\JWTMiddleware;
use App\Models\Universidad\Especialidad;
use Illuminate\Support\Facades\Route;

Route::middleware([JWTMiddleware::class, 'api'])->group(
    function (): void {
        Route::prefix('v1')->group(function (): void {
            Route::get('/especialidades', [EspecialidadController::class, 'indexAll'])->middleware('can:ver especialidades');
            Route::get('/especialidades/paginated', [EspecialidadController::class, 'index'])->middleware('can:ver especialidades');
            Route::post('/especialidades', [EspecialidadController::class, 'store'])->middleware('can:manage especialidades');
            Route::get('/especialidades/{id}', [EspecialidadController::class, 'show'])->middleware([AuthzMiddleware::class . ':ver especialidades,' . Especialidad::class]);
            Route::put('/especialidades/{id}', [EspecialidadController::class, 'update'])->middleware([AuthzMiddleware::class . ':manage especialidades,' . Especialidad::class]);
            Route::delete('/especialidades/{id}', [EspecialidadController::class, 'destroy'])->middleware([AuthzMiddleware::class . ':manage especialidades,' . Especialidad::class]);
            Route::get('/especialidades/nombre/{nombre}', [EspecialidadController::class, 'showByName'])->middleware('can:ver especialidades');
        });
    }
);