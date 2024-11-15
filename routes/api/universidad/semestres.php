<?php

use App\Http\Controllers\Universidad\SemestreController;
use Illuminate\Support\Facades\Route;


Route::get('semestres', [SemestreController::class, 'indexAll'])->middleware('can:ver semestres');
Route::get('semestres/paginated', [SemestreController::class, 'index'])->middleware('can:ver semestres');
Route::get('semestres/last', [SemestreController::class, 'getLastSemestre']);
Route::post('semestres', [SemestreController::class, 'store'])->middleware('can:manage semestres');
Route::delete('semestres/eliminarSemestres', [SemestreController::class, 'destroyMultiple']);
Route::get('semestres/{id}', [SemestreController::class, 'show'])->middleware('can:ver semestres');
Route::put('semestres/{id}', [SemestreController::class, 'update'])->middleware('can:manage semestres');
Route::delete('semestres/{id}', [SemestreController::class, 'destroy'])->middleware('can:manage semestres');
Route::put('semestres/{id}', [SemestreController::class, 'update'])->middleware('can:manage semestres');
Route::delete('semestres/{id}', [SemestreController::class, 'destroy'])->middleware('can:managesemestres');


Route::get('semestreActual', [SemestreController::class, 'obtenerSemestreActual']);
