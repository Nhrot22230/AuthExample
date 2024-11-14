<?php

use App\Http\Controllers\Universidad\EspecialidadController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Especialidad;
use Illuminate\Support\Facades\Route;

Route::middleware("can:unidades")
    ->group(function () {
        Route::get('especialidades', [EspecialidadController::class, 'indexAll']);
        Route::get('especialidades/paginated', [EspecialidadController::class, 'index']);
        Route::post('especialidades', [EspecialidadController::class, 'store']);
    });


Route::middleware(AuthzMiddleware::class . ":especialidades," . Especialidad::class)
    ->group(function () {
        Route::get('especialidades/{id}', [EspecialidadController::class, 'show']);
        Route::put('especialidades/{id}', [EspecialidadController::class, 'update']);
        Route::delete('especialidades/{id}', [EspecialidadController::class, 'destroy']);
    });

Route::get('especialidades/nombre/{nombre}', [EspecialidadController::class, 'showByName']);
