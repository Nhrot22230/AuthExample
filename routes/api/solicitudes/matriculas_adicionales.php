<?php

use App\Http\Controllers\Solicitudes\MatriculaAdicionalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Matricula\CartaPresentacionController;
use App\Http\Controllers\Matricula\HorarioActividadController;


Route::post('matriculas-adicionales', [MatriculaAdicionalController::class, 'store']);
Route::get('matriculas-adicionales/facultad/{facultadId}', [MatriculaAdicionalController::class, 'getByFacultad']);
Route::get('matriculas-adicionales/{id}', [MatriculaAdicionalController::class, 'getByEspecialidad']);
Route::get('matriculas-adicionales/estudiante/{estudianteId}', [MatriculaAdicionalController::class, 'getByEstudiante']);
Route::get('horarios/cursos/{cursoId}', [MatriculaAdicionalController::class, 'getHorariosByCurso']);
Route::get('matricula-adicional/{id}', [MatriculaAdicionalController::class, 'getById']);
Route::patch('matricula-adicional/{id}/rechazar', [MatriculaAdicionalController::class, 'rechazar']);
Route::patch('matricula-adicional/aprobar-dc/{id}', [MatriculaAdicionalController::class, 'aprobarPorDC']);
Route::patch('matricula-adicional/aprobar-sa/{id}', [MatriculaAdicionalController::class, 'aprobarPorSA']);
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
Route::patch('carta-presentacion/{id}/rechazar', [CartaPresentacionController::class, 'rechazarCarta']);
Route::patch('carta-presentacion/{id}/aprobar-secretaria', [CartaPresentacionController::class, 'aprobarCartaSecretaria']);
Route::get('solicitudes/profesor/{profesorId}', [CartaPresentacionController::class, 'getByProfesor']);
Route::put('/cartas/aprobar/{horario_id}', [CartaPresentacionController::class, 'aprobarPorHorario']);


Route::prefix('cartas')->group(function () {
    // Ruta para solicitar actividades
    Route::patch('{id}/solicitar-actividades', [CartaPresentacionController::class, 'solicitarActividades']);
});


Route::prefix('horarios/{horarioId}/actividades')->group(function () {
    // Mostrar todas las actividades de un horario
    Route::get('/', [HorarioActividadController::class, 'index']);
    
    // Crear una nueva actividad para un horario
    Route::post('/', [HorarioActividadController::class, 'store']);
    
    // Actualizar una actividad existente
    Route::put('/{actividadId}', [HorarioActividadController::class, 'update']);
    
    // Eliminar una actividad de un horario
    Route::delete('/{actividadId}', [HorarioActividadController::class, 'destroy']);
    
    // Verificar si un horario tiene actividades
    Route::get('/verificar', [HorarioActividadController::class, 'verificarActividades']);
    
    // Solicitar que el profesor agregue actividades
    Route::post('/solicitar', [HorarioActividadController::class, 'solicitarActividades']);
});
