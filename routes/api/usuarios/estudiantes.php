<?php

use App\Http\Controllers\Usuarios\EstudianteController;
use Illuminate\Support\Facades\Route;

Route::middleware("can:usuarios")->group(function () {
    Route::get('estudiantes', [EstudianteController::class, 'index']);
    Route::post('estudiantes', [EstudianteController::class, 'store']);
    Route::post('estudiantes/multiple', [EstudianteController::class, 'storeMultiple']);
    Route::get('estudiantes/{codEstudiante}', [EstudianteController::class, 'show']);
    Route::put('estudiantes/{id}', [EstudianteController::class, 'update']);
    Route::delete('estudiantes/{codEstudiante}', [EstudianteController::class, 'destroy']);
});

