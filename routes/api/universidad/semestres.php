<?php

use App\Http\Controllers\Universidad\SemestreController;
use Illuminate\Support\Facades\Route;

Route::prefix('semestres')->group(function () {
    Route::get('/', [SemestreController::class, 'indexAll']);
    Route::get('/paginated', [SemestreController::class, 'index']);
    Route::get('/last', [SemestreController::class, 'getLastSemestre']);

    Route::middleware("can:semestres")->group(function () {
        Route::get('/{id}', [SemestreController::class, 'show']);
        Route::put('/{id}', [SemestreController::class, 'update']);
        Route::post('/', [SemestreController::class, 'store']);
        Route::delete('/eliminar/{id}', [SemestreController::class, 'destroy']);
        Route::delete('/eliminarSemestres', [SemestreController::class, 'destroyMultiple']);
    });
});

Route::get('semestreActual', [SemestreController::class, 'obtenerSemestreActual']);

