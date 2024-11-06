<?php

use App\Http\Controllers\Matricula\HorarioController;
use Illuminate\Support\Facades\Route;


Route::get('estudiantes/{estudianteId}/cursos', [HorarioController::class, 'obtenerCursosEstudiante']);
Route::get('horarios/{horarioId}/jps', [HorarioController::class, 'obtenerJps']);
Route::get('estudiantes/{estudianteId}/encuestas-docentes', [HorarioController::class, 'obtenerEncuestasDocentesEstudiante']);
