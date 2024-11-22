<?php

use App\Http\Controllers\Universidad\AreaController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Area;
use Illuminate\Support\Facades\Route;

Route::prefix('areas')->group(function () {
    Route::get('/', [AreaController::class, 'index']);

    Route::middleware("can:unidades")->group(function () {
        Route::post('/', [AreaController::class, 'store']);
        Route::get('/{entity_id}', [AreaController::class, 'show']);
        Route::put('/{entity_id}', [AreaController::class, 'update']);
        Route::delete('/{entity_id}', [AreaController::class, 'destroy']);
    });

    Route::middleware(AuthzMiddleware::class . ":areas," . Area::class)->group(function () {
        Route::get('/{entity_id}', [AreaController::class, 'show']);
        Route::put('/{entity_id}', [AreaController::class, 'update']);
        Route::delete('/{entity_id}', [AreaController::class, 'destroy']);
    });
});
