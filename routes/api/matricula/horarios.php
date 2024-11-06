<?php

use App\Http\Controllers\Matricula\HorarioController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JWTMiddleware::class, 'api'])->group(
    function (): void {
        Route::prefix('v1')->group(function (): void {
            Route::get('/estudiantes/{estudianteId}/cursos', [HorarioController::class, 'obtenerCursosEstudiante']);
            Route::get('/horarios/{horarioId}/jps', [HorarioController::class, 'obtenerJps']);
            Route::get('/estudiantes/{estudianteId}/encuestas-docentes', [HorarioController::class, 'obtenerEncuestasDocentesEstudiante']);
        });
    }
);