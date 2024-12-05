<?php

use App\Http\Controllers\Universidad\DepartamentoController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Departamento;
use Illuminate\Support\Facades\Route;

Route::prefix('departamentos')->group(function () {
    Route::get('/', [DepartamentoController::class, 'indexAll']);
    Route::get('/paginated', [DepartamentoController::class, 'index']);
    Route::get('/nombre/{nombre}', [DepartamentoController::class, 'showByName']);

    Route::middleware("can:unidades")->group(function () {
        Route::post('/', [DepartamentoController::class, 'store']);
        Route::get('/{entity_id}', [DepartamentoController::class, 'show']);
        Route::put('/{entity_id}', [DepartamentoController::class, 'update']);
        Route::delete('/{entity_id}', [DepartamentoController::class, 'destroy']);
        Route::post('/multiple', [DepartamentoController::class, 'storeMultiple']);
    });

    Route::middleware(AuthzMiddleware::class . ":departamentos," . Departamento::class)->group(function () {
        Route::get('/{entity_id}', [DepartamentoController::class, 'show']);
        Route::put('/{entity_id}', [DepartamentoController::class, 'update']);
        Route::delete('/{entity_id}', [DepartamentoController::class, 'destroy']);
        Route::post('/multiple', [DepartamentoController::class, 'storeMultiple']);
    });
});
