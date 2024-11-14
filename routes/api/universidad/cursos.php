<?php

use App\Http\Controllers\Universidad\CursoController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Curso;
use Illuminate\Support\Facades\Route;

Route::prefix('cursos')
    ->group(function () {
        Route::middleware("can:unidades")
            ->group(function () {
                Route::get('/', [CursoController::class, 'index']);
                Route::get('/paginated', [CursoController::class, 'indexPaginated']);
                Route::post('/', [CursoController::class, 'store']);
            });

        Route::middleware(AuthzMiddleware::class . ":cursos," . Curso::class)
            ->group(function () {
                Route::get('/{id}', [CursoController::class, 'show']);
                Route::put('/{id}', [CursoController::class, 'update']);
                Route::delete('/{id}', [CursoController::class, 'destroy']);
            });

        Route::get('/codigo/{codigo}', [CursoController::class, 'getByCodigo']);
        Route::get('/{cursoId}/docentes', [CursoController::class, 'obtenerDocentesPorCurso']);
        Route::get('/{cursoId}/horarios', [CursoController::class, 'obtenerHorariosPorCurso']);
    });
