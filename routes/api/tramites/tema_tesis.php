<?php

use App\Http\Controllers\TemaTesisController;
use App\Http\Controllers\ProcesoAprobacionController;
use Illuminate\Support\Facades\Route;

Route::prefix('temas-de-tesis')->group(function () {
    Route::get('autor', [TemaTesisController::class, 'index']);
    Route::get('asesor', [ProcesoAprobacionController::class,'indexByAsesor']);
    Route::post('registro', [TemaTesisController::class, 'store']);
    Route::get('{id}', [TemaTesisController::class, 'show']);
    Route::get('{idTesis}/procesos-aprobacion', [ProcesoAprobacionController::class, 'indexByTema']);
});

Route::get('procesos-aprobacion/{idProceso}', [ProcesoAprobacionController::class, 'show']);
Route::get('procesos-aprobacion/areas/{idArea}', [ProcesoAprobacionController::class, 'indexByArea']);

//Route::put('temas-de-tesis/{id}', [TemaTesisController::class, 'update']);
//Route::get('temas-de-tesis/estudiante/{estudiante_id}', [TemaTesisController::class, 'indexTemasEstudianteId']);
//Route::get('temas-de-tesis/evaluadores/{usuario_id}', [TemaTesisController::class, 'indexTemasPendientesUsuarioId']);
//Route::get('temas-de-tesis/areas/{estudiante_id}', [TemaTesisController::class, 'listarAreasEspecialidad']);
//Route::get('temas-de-tesis/docentes/{estudiante_id}', [TemaTesisController::class, 'listarDocentesEspecialidad']);
//Route::post('temas-de-tesis/aprobar/{tema_tesis_id}', [TemaTesisController::class, 'aprobarTemaUsuario']);
//Route::put('temas-de-tesis/rechazar/{tema_tesis_id}', [TemaTesisController::class, 'rechazarTemaUsuario']);
//Route::get('temas-de-tesis/estado/{tema_tesis_id}', [TemaTesisController::class, 'verDetalleTema']);
//Route::get('temas-de-tesis/descargar/{tema_tesis_id}', [TemaTesisController::class, 'descargarArchivo']);
