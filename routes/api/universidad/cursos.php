<?php

use App\Http\Controllers\Universidad\CursoController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Curso;
use Illuminate\Support\Facades\Route;

Route::prefix('cursos')->group(function () {
    Route::get('/', [CursoController::class, 'index']);
    Route::get('/paginated', [CursoController::class, 'indexPaginated']);
    Route::get('/codigo/{codigo}', [CursoController::class, 'getByCodigo']);
    Route::get('/{cursoId}/docentes', [CursoController::class, 'obtenerDocentesPorCurso']);
    Route::get('/{cursoId}/horarios', [CursoController::class, 'obtenerHorariosPorCurso']);

    Route::middleware("can:unidades")->group(function () {
        Route::post('/', [CursoController::class, 'store']);
        Route::get('/{entity_id}', [CursoController::class, 'show']);
        Route::put('/{entity_id}', [CursoController::class, 'update']);
        Route::delete('/{entity_id}', [CursoController::class, 'destroy']);
    });

    Route::middleware(AuthzMiddleware::class . ":cursos," . Curso::class)->group(function () {
        Route::get('/{entity_id}', [CursoController::class, 'show']);
        Route::put('/{entity_id}', [CursoController::class, 'update']);
        Route::delete('/{entity_id}', [CursoController::class, 'destroy']);

        Route::get('/{entity_id}/cursosDocente', [CursoController::class, 'obtenerCursosPorDocente']);
        Route::get('/{entity_id}/detalle', [CursoController::class, 'obtenerCursoPorId']);
        Route::get('/{entity_id}/horarios', [CursoController::class, 'obtenerHorariosPorDocenteYCursos']);
        Route::get('/{entity_id}/alumnos', [CursoController::class, 'obtenerAlumnosPorHorario']);
    });
});
