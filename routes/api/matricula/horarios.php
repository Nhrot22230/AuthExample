<?php

use App\Http\Controllers\Matricula\HorarioController;
use Illuminate\Support\Facades\Route;


Route::get('estudiantes/{estudianteId}/cursos-encuestas', [HorarioController::class, 'obtenerCursosEstudiante']);
Route::get('horarios/{horarioId}/jps', [HorarioController::class, 'obtenerJps']);
Route::get('estudiantes/{estudianteId}/encuestas-docentes', [HorarioController::class, 'obtenerEncuestasDocentesEstudiante']);
Route::post('horarios/delegado', [HorarioController::class, 'obtenerDelegado']);
Route::post('horariosDA/horarios-jps/GO', [HorarioController::class, 'obtenerHorariosConJefes']);
Route::post('horariosDA/jefe-practica/eliminar/GO', [HorarioController::class, 'eliminarJefePractica']);
Route::get('usuariosDA/estudiantes-docentes/GAAA', [HorarioController::class, 'listarUsuariosEstudiantesYDocentes']);
Route::post('horariosDA/jefe-practica/agregar/GO', [HorarioController::class, 'agregarJefePracticaAHorario']);