<?php

use App\Http\Controllers\Universidad\SemestreController;
use Illuminate\Support\Facades\Route;


Route::middleware("can:semestres")
    ->group(function () {
        Route::get('semestres', [SemestreController::class, 'indexAll']);
        Route::get('semestres/paginated', [SemestreController::class, 'index']);
        Route::get('semestres/{id}', [SemestreController::class, 'show']);
        Route::put('semestres/{id}', [SemestreController::class, 'update']);
        Route::post('semestres', [SemestreController::class, 'store']);
        Route::delete('semestres/{id}', [SemestreController::class, 'destroy']);
        Route::delete('semestres/eliminarSemestres', [SemestreController::class, 'destroyMultiple']);
    });
Route::get('semestres/last', [SemestreController::class, 'getLastSemestre']);
Route::get('semestreActual', [SemestreController::class, 'obtenerSemestreActual']);
