<?php

use App\Http\Controllers\Solicitudes\MatriculaAdicionalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Matricula\CartaPresentacionController;


Route::post('matriculas-adicionales', [MatriculaAdicionalController::class, 'store']);
Route::get('matriculas-adicionales/facultad/{facultadId}', [MatriculaAdicionalController::class, 'getByFacultad'])->middleware('can:ver matriculas_facultad');
Route::get('matriculas-adicionales/{id}', [MatriculaAdicionalController::class, 'getByEspecialidad'])->middleware('can:ver matriculas_especialidad');
Route::get('matriculas-adicionales/estudiante/{estudianteId}', [MatriculaAdicionalController::class, 'getByEstudiante']);
Route::get('horarios/cursos/{cursoId}', [MatriculaAdicionalController::class, 'getHorariosByCurso']);
Route::get('matricula-adicional/{id}', [MatriculaAdicionalController::class, 'getById']);
Route::patch('matricula-adicional/{id}/rechazar', [MatriculaAdicionalController::class, 'rechazar'])->middleware('can:ver matriculas_especialidad');
Route::patch('matricula-adicional/aprobar-dc/{id}', [MatriculaAdicionalController::class, 'aprobarPorDC'])->middleware('can:ver matriculas_especialidad');
Route::patch('matricula-adicional/aprobar-sa/{id}', [MatriculaAdicionalController::class, 'aprobarPorSA'])->middleware('can:ver matriculas_especialidad');


Route::get('solicitudes/carta/{estudianteId}', [CartaPresentacionController::class, 'getByEstudiante']);
Route::get('solicitudes/{id}', [CartaPresentacionController::class, 'getSolicitudDetalle']);
Route::get('estudiantes/{estudianteId}/cursos', [CartaPresentacionController::class, 'getCursosPorEstudiante']);
Route::post('/carta-presentacion', [CartaPresentacionController::class, 'store']);
Route::get('solicitudes/por-especialidad/{especialidadId}', [CartaPresentacionController::class, 'getByEspecialidad']);

