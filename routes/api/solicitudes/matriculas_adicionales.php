<?php

use App\Http\Controllers\Solicitudes\MatriculaAdicionalController;
use Illuminate\Support\Facades\Route;


Route::post('matriculas-adicionales', [MatriculaAdicionalController::class, 'store']);
Route::get('matriculas-adicionales/facultad/{facultadId}', [MatriculaAdicionalController::class, 'getByFacultad']);
Route::get('matriculas-adicionales/{id}', [MatriculaAdicionalController::class, 'getByEspecialidad']);
Route::get('matriculas-adicionales/estudiante/{estudianteId}', [MatriculaAdicionalController::class, 'getByEstudiante']);
Route::get('horarios/cursos/{cursoId}', [MatriculaAdicionalController::class, 'getHorariosByCurso']);
Route::get('matricula-adicional/{id}', [MatriculaAdicionalController::class, 'getById']);
Route::patch('matricula-adicional/{id}/rechazar', [MatriculaAdicionalController::class, 'rechazar']);
Route::patch('matricula-adicional/aprobar-dc/{id}', [MatriculaAdicionalController::class, 'aprobarPorDC']);
Route::patch('matricula-adicional/aprobar-sa/{id}', [MatriculaAdicionalController::class, 'aprobarPorSA']);
