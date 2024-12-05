<?php

use App\Http\Controllers\Universidad\EspecialidadController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Especialidad;
use Illuminate\Support\Facades\Route;

Route::prefix('especialidades')->group(function () {
    Route::get('/', [EspecialidadController::class, 'indexAll']);
    Route::get('/paginated', [EspecialidadController::class, 'index']);
    Route::get('/nombre/{nombre}', [EspecialidadController::class, 'showByName']);
    
    Route::middleware("can:unidades")->group(function () {
        Route::post('/', [EspecialidadController::class, 'store']);
        Route::get('/{entity_id}', [EspecialidadController::class, 'show']);
        Route::put('/{entity_id}', [EspecialidadController::class, 'update']);
        Route::delete('/{entity_id}', [EspecialidadController::class, 'destroy']);
        Route::post('/multiple', [EspecialidadController::class, 'storeMultiple']);

    });

    Route::middleware(AuthzMiddleware::class . ":especialidades," . Especialidad::class)->group(function () {
        Route::get('/{entity_id}', [EspecialidadController::class, 'show']);
        Route::put('/{entity_id}', [EspecialidadController::class, 'update']);
        Route::delete('/{entity_id}', [EspecialidadController::class, 'destroy']);
        Route::post('/multiple', [EspecialidadController::class, 'storeMultiple']);

    });
});
