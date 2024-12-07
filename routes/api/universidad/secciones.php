<?php

use App\Http\Controllers\Universidad\SeccionController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Seccion;
use Illuminate\Support\Facades\Route;

Route::prefix('secciones')->group(function () {
    Route::get('/', [SeccionController::class, 'indexAll']);
    Route::get('/paginated', [SeccionController::class, 'index']);


    Route::middleware("can:unidades")->group(function () {
        Route::post('/', [SeccionController::class, 'store']);
        Route::get('/{entity_id}', [SeccionController::class, 'show']);
        Route::put('/{entity_id}', [SeccionController::class, 'update']);
        Route::delete('/{entity_id}', [SeccionController::class, 'destroy']);
        Route::post('/multiple', [SeccionController::class, 'storeMultiple']);
    });

    Route::middleware(AuthzMiddleware::class . ":secciones," . Seccion::class)->group(function () {
        Route::get('/{entity_id}', [SeccionController::class, 'show']);
        Route::put('/{entity_id}', [SeccionController::class, 'update']);
        Route::delete('/{entity_id}', [SeccionController::class, 'destroy']);
        Route::post('/multiple', [SeccionController::class, 'storeMultiple']);
    });
});
