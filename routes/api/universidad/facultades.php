<?php

use App\Http\Controllers\Universidad\FacultadController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Facultad;
use Illuminate\Support\Facades\Route;


Route::middleware("can:unidades")
    ->group(function () {
        Route::get('facultades', [FacultadController::class, 'indexAll']);
        Route::get('facultades/paginated', [FacultadController::class, 'index']);
        Route::post('facultades', [FacultadController::class, 'store']);
    });

Route::middleware(AuthzMiddleware::class . ":facultades," . Facultad::class)
    ->group(function () {
        Route::get('facultades/{id}', [FacultadController::class, 'show'])->middleware([AuthzMiddleware::class . ':ver facultades,' . Facultad::class]);
        Route::put('facultades/{id}', [FacultadController::class, 'update'])->middleware([AuthzMiddleware::class . ':manage facultades,' . Facultad::class]);
        Route::delete('facultades/{id}', [FacultadController::class, 'destroy'])->middleware([AuthzMiddleware::class . ':manage facultades,' . Facultad::class]);
    });

Route::get('facultades/nombre/{nombre}', [FacultadController::class, 'showByName'])->middleware('can:ver facultades');
