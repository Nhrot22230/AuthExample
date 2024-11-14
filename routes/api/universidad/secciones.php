<?php

use App\Http\Controllers\Universidad\SeccionController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Seccion;
use Illuminate\Support\Facades\Route;

Route::middleware("can:unidades")
    ->group(function () {
        Route::get('secciones', [SeccionController::class, 'indexAll']);
        Route::get('secciones/paginated', [SeccionController::class, 'index']);
        Route::post('secciones', [SeccionController::class, 'store']);
    });


Route::middleware(AuthzMiddleware::class . ":secciones," . Seccion::class)
    ->group(function () {
        Route::get('secciones/{id}', [SeccionController::class, 'show']);
        Route::put('secciones/{id}', [SeccionController::class, 'update']);
        Route::delete('secciones/{id}', [SeccionController::class, 'destroy']);
    });