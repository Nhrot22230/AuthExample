<?php

use App\Http\Controllers\Tramites\TemaDeTesisController;
use Illuminate\Support\Facades\Route;


Route::get('temas-de-tesis', [TemaDeTesisController::class, 'indexPaginated'])->middleware('can:ver temas de tesis');
Route::get('temas-de-tesis/{id}', [TemaDeTesisController::class, 'show']);
Route::put('temas-de-tesis/{id}', [TemaDeTesisController::class, 'update'])->middleware('can:manage temas de tesis');
Route::get('temas-de-tesis/estudiante/{estudiante_id}', [TemaDeTesisController::class, 'indexTemasEstudianteId']);
Route::get('temas-de-tesis/evaluadores/{usuario_id}', [TemaDeTesisController::class, 'indexTemasPendientesUsuarioId']);
Route::get('temas-de-tesis/areas/{estudiante_id}', [TemaDeTesisController::class, 'listarAreasEspecialidad']);
Route::get('temas-de-tesis/docentes/{estudiante_id}', [TemaDeTesisController::class, 'listarDocentesEspecialidad']);
Route::post('temas-de-tesis/registro', [TemaDeTesisController::class, 'registrarTema']);
Route::post('temas-de-tesis/aprobar/{tema_tesis_id}', [TemaDeTesisController::class, 'aprobarTemaUsuario']);
Route::put('temas-de-tesis/rechazar/{tema_tesis_id}', [TemaDeTesisController::class, 'rechazarTemaUsuario']);
Route::get('temas-de-tesis/estado/{tema_tesis_id}', [TemaDeTesisController::class, 'verDetalleTema']);
