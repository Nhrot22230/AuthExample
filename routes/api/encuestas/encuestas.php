<?php

use App\Http\Controllers\Encuestas\EncuestaController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JWTMiddleware::class, 'api'])->group(
    function (): void {
        Route::prefix('v1')->group(function (): void {
            Route::get('/encuestas/{encuestaId}/horarios/{horarioId}/{jpId?}', [EncuestaController::class, 'obtenerDetalleEncuesta']);
            Route::post('/encuestas/{encuestaId}/horarios/{horarioId}/respuestas', [EncuestaController::class, 'registrarRespuestas']);
            Route::get('/encuestas/{encuestaId}/cursos', [EncuestaController::class, 'obtenerCursosEncuesta']);
            Route::get('/resultados/docentes/encuestas/{encuestaId}/horarios/{horarioId}', [EncuestaController::class, 'obtenerResultadosDetalleDocente']);
            Route::get('/resultados/jefes-practica/encuestas/{encuestaId}/jp-horarios/{jpHorarioId}', [EncuestaController::class, 'obtenerResultadosDetalleJp']);
            Route::get('/encuestas/{especialidad_id}/{tipo_encuesta}', [EncuestaController::class, 'indexEncuesta']);
            Route::get('/encuestas-nueva-cursos/{especialidad_id}', [EncuestaController::class, 'indexCursoSemestreEspecialidad']);
            Route::get('/encuestas-nueva-cant/{especialidad_id}/{tipo_encuesta}', [EncuestaController::class, 'countPreguntasLatestEncuesta']);
            Route::get('/encuestas-nueva-preg/{especialidad_id}/{tipo_encuesta}', [EncuestaController::class, 'obtenerPreguntasUltimaEncuesta']);
            Route::post('/encuestas-nueva/{especialidad_id}/{tipo_encuesta}', [EncuestaController::class, 'registrarNuevaEncuesta']);
            Route::get('/encuestas-cursos/{encuesta_id}', [EncuestaController::class, 'mostrarCursos']);
            Route::get('/encuestas-preguntas/{encuesta_id}', [EncuestaController::class, 'listarPreguntas']);
            Route::put('/encuestas/{especialidad_id}/{encuesta_id}', [EncuestaController::class, 'gestionarEncuesta']);
        });
    }
);