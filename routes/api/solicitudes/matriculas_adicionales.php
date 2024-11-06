<?php

use App\Http\Controllers\Solicitudes\MatriculaAdicionalController;
use Illuminate\Support\Facades\Route;


Route::post('matriculas-adicionales', [MatriculaAdicionalController::class, 'store'])->middleware('can:manage matriculas_adicionales');
Route::get('matriculas-adicionales/facultad/{facultadId}', [MatriculaAdicionalController::class, 'getByFacultad'])->middleware('can:ver matriculas_facultad');
Route::get('matriculas-adicionales/{id}', [MatriculaAdicionalController::class, 'getByEspecialidad'])->middleware('can:ver matriculas_especialidad');
Route::get('matriculas-adicionales/estudiante/{estudianteId}', [MatriculaAdicionalController::class, 'getByEstudiante'])->middleware('can:ver mis matriculas_adicionales');
Route::get('horarios/cursos/{cursoId}', [MatriculaAdicionalController::class, 'getHorariosByCurso'])->middleware('can:ver horarios');
Route::get('matricula-adicional/{id}', [MatriculaAdicionalController::class, 'getById'])->middleware('can:ver mis matriculas_adicionales');
Route::patch('matricula-adicional/{id}/rechazar', [MatriculaAdicionalController::class, 'rechazar'])->middleware('can:ver matriculas_especialidad');
Route::patch('matricula-adicional/aprobar-dc/{id}', [MatriculaAdicionalController::class, 'aprobarPorDC'])->middleware('can:ver matriculas_especialidad');
Route::patch('matricula-adicional/aprobar-sa/{id}', [MatriculaAdicionalController::class, 'aprobarPorSA'])->middleware('can:ver matriculas_especialidad');
