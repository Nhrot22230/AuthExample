<?php

use App\Http\Controllers\Usuarios\DocenteController;
use Illuminate\Support\Facades\Route;

Route::prefix('docentes')->group(function () {
    Route::get('/', [DocenteController::class, 'index']);

    Route::middleware("can:usuarios")->group(function () {
        Route::post('/', [DocenteController::class, 'store']);
        Route::post('multiple', [DocenteController::class, 'storeMultiple']);
        Route::get('{codDocente}', [DocenteController::class, 'show']);
        Route::put('{codDocente}', [DocenteController::class, 'update']);
        Route::delete('{codDocente}', [DocenteController::class, 'destroy']);
    });
});

