<?php

use App\Http\Controllers\Universidad\AreaController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Area;
use Illuminate\Support\Facades\Route;

Route::prefix('areas')
    ->group(function () {
        Route::middleware("can:unidades")
            ->group(function () {
                Route::get('/', [AreaController::class, 'indexAll']);
                Route::get('/paginated', [AreaController::class, 'index']);
                Route::post('/', [AreaController::class, 'store']);
            });

        Route::middleware(AuthzMiddleware::class . ":areas," . Area::class)
            ->group(function () {
                Route::get('/{id}', [AreaController::class, 'show']);
                Route::put('/{id}', [AreaController::class, 'update']);
                Route::delete('/{id}', [AreaController::class, 'destroy']);
            });
    });
