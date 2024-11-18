<?php

use App\Http\Controllers\Universidad\FacultadController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Facultad;
use Illuminate\Support\Facades\Route;

Route::prefix('facultades')->group(function () {
    Route::get('/', [FacultadController::class, 'indexAll']);
    Route::get('/paginated', [FacultadController::class, 'index']);
    Route::get('/nombre/{nombre}', [FacultadController::class, 'showByName']);
    
    Route::middleware("can:unidades")->group(function () {
        Route::post('/', [FacultadController::class, 'store']);
        Route::get('/{entity_id}', [FacultadController::class, 'show']);
        Route::put('/{entity_id}', [FacultadController::class, 'update']);
        Route::delete('/{entity_id}', [FacultadController::class, 'destroy']);
    });

    Route::middleware(AuthzMiddleware::class . ":facultades," . Facultad::class)->group(function () {
        Route::get('/{entity_id}', [FacultadController::class, 'show']);
        Route::put('/{entity_id}', [FacultadController::class, 'update']);
        Route::delete('/{entity_id}', [FacultadController::class, 'destroy']);
    });
});
