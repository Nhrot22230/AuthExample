<?php

use App\Http\Controllers\Usuarios\EstudianteController;
use Illuminate\Support\Facades\Route;

Route::get('estudiantes', [EstudianteController::class, 'index'])->middleware('can:ver usuarios');
Route::post('estudiantes', [EstudianteController::class, 'store'])->middleware('can:manage usuarios');
Route::post('estudiantes/multiple', [EstudianteController::class, 'storeMultiple'])->middleware('can:manage usuarios');
Route::get('estudiantes/{codEstudiante}', [EstudianteController::class, 'show'])->middleware('can:ver usuarios');
Route::put('estudiantes/{id}', [EstudianteController::class, 'update'])->middleware('can:manage usuarios');
Route::delete('estudiantes/{codEstudiante}', [EstudianteController::class, 'destroy'])->middleware('can:manage usuarios');
