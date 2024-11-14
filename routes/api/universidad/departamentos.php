<?php

use App\Http\Controllers\Universidad\DepartamentoController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Departamento;
use Illuminate\Support\Facades\Route;

Route::prefix('departamentos')
    ->group(function () {
        Route::middleware("can:unidades")
            ->group(function () {
                Route::get('/', [DepartamentoController::class, 'indexAll']);
                Route::get('/paginated', [DepartamentoController::class, 'index']);
                Route::post('/', [DepartamentoController::class, 'store']);
                Route::get('/nombre/{nombre}', [DepartamentoController::class, 'showByName'])->middleware('can:ver departamentos');
            });

        Route::middleware(AuthzMiddleware::class . ":departamentos," . Departamento::class)
            ->group(function () {
                Route::get('/{id}', [DepartamentoController::class, 'show']);
                Route::put('/{id}', [DepartamentoController::class, 'update']);
                Route::delete('/{id}', [DepartamentoController::class, 'destroy']);
            });
    });
