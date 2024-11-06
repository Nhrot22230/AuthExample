<?php

use App\Http\Controllers\Universidad\CursoController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Curso;
use Illuminate\Support\Facades\Route;

Route::get('cursos', [CursoController::class, 'index'])->middleware('can:ver cursos');
Route::get('cursos/paginated', [CursoController::class, 'indexPaginated'])->middleware('can:ver cursos');
Route::get('cursos/codigo/{codigo}', [CursoController::class, 'getByCodigo'])->middleware('can:ver cursos');
Route::post('cursos', [CursoController::class, 'store'])->middleware('can:manage cursos');
Route::get('cursos/{id}', [CursoController::class, 'show'])->middleware([AuthzMiddleware::class . ':ver cursos,' . Curso::class]);
Route::put('cursos/{id}', [CursoController::class, 'update'])->middleware([AuthzMiddleware::class . ':manage cursos,' . Curso::class]);
Route::delete('cursos/{id}', [CursoController::class, 'destroy'])->middleware([AuthzMiddleware::class . ':manage cursos,' . Curso::class]);

Route::get('cursos/{cursoId}/docentes', [CursoController::class, 'obtenerDocentesPorCurso']);
Route::get('cursos/{cursoId}/horarios', [CursoController::class, 'obtenerHorariosPorCurso']);
