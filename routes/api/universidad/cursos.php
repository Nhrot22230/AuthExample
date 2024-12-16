<?php

use App\Http\Controllers\Universidad\CursoController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Curso;
use Illuminate\Support\Facades\Route;

Route::prefix('cursos')->group(function () {
    Route::get('/', [CursoController::class, 'index']);
    Route::get('/indexPaginated', [CursoController::class, 'indexPaginated']);
    Route::post('/cursosDocente', [CursoController::class, 'obtenerCursosPorDocente']);
    Route::post('/cursosEstudiante', [CursoController::class, 'obtenerCursosPorEstudiante']);
    Route::post('/actualizar-delegado', [CursoController::class, 'actualizarDelegado']);
    Route::post('/horarios', [CursoController::class, 'obtenerHorariosPorDocenteYCursos']);
    Route::post('/detalle', [CursoController::class, 'obtenerCursoPorId']);
    Route::get('/paginated', [CursoController::class, 'indexPaginated']);
    Route::get('/codigo/{codigo}', [CursoController::class, 'getByCodigo']);
    Route::get('/{cursoId}/docentes', [CursoController::class, 'obtenerDocentesPorCurso']);
    Route::get('/{cursoId}/horarios', [CursoController::class, 'obtenerHorariosPorCurso']);
    Route::post('/alumnos', [CursoController::class, 'obtenerAlumnosPorHorario']);
    Route::get('/{entity_id}', [CursoController::class, 'show']);
    
    //Route::middleware("can:unidades")->group(function () {
    Route::post('/', [CursoController::class, 'store']);
    Route::put('/{entity_id}', [CursoController::class, 'update']);
    Route::delete('/{entity_id}', [CursoController::class, 'destroy']);
    Route::post('/multiple', [CursoController::class, 'storeMultiple']);
    //});
    
    Route::middleware(AuthzMiddleware::class . ":cursos," . Curso::class)->group(function () {
        Route::put('/{entity_id}', [CursoController::class, 'update']);
        Route::delete('/{entity_id}', [CursoController::class, 'destroy']);
    });
});