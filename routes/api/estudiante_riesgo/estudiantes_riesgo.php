<?php

use App\Http\Controllers\EstudianteRiesgo\EstudianteRiesgoController;
use Illuminate\Support\Facades\Route;


Route::get('estudiantesRiesgo/listar_profesor', [EstudianteRiesgoController::class, 'listar_por_especialidad_profesor']);
Route::get('estudiantesRiesgoDA/listar_director/GA', [EstudianteRiesgoController::class, 'listar_por_especialidad_director']);
Route::post('estudiantesRiesgoDA/listar_informes/GA', [EstudianteRiesgoController::class, 'listar_informes_estudiante']);
Route::put('estudiantesRiesgo/actualizar_informe', [EstudianteRiesgoController::class, 'actualizar_informe_estudiante']);
Route::post('estudiantesRiesgoDA/carga_alumnos', [EstudianteRiesgoController::class, 'carga_alumnos_riesgo']);
Route::post('estudiantesRiesgo/manage_informes', [EstudianteRiesgoController::class, 'manage_informes']);
Route::get('estudiantesRiesgo/obtener_datos_semana', [EstudianteRiesgoController::class, 'obtener_datos_semana']);
Route::get('estudiantesRiesgo/obtener_estadisticas_informes', [EstudianteRiesgoController::class, 'obtener_estadisticas_informes']);
Route::get('estudiantesRiesgo/listar_informes_director', [EstudianteRiesgoController::class, 'listar_informes_director']);
Route::get('estudiantesRiesgo/listar_semanas_existentes/{id}', [EstudianteRiesgoController::class, 'listar_semanas_existentes']);
Route::delete('estudiantesRiesgo/eliminar_semana', [EstudianteRiesgoController::class, 'eliminar_semana']);
Route::post('estudiantesRiesgo/crear-por-especialidad', [EstudianteRiesgoController::class, 'crearInformePorEspecialidad']);
Route::post('estudiantesRiesgoDA/semanas-por-especialidad', [EstudianteRiesgoController::class, 'obtenerSemanasPorEspecialidad']);
Route::get('estudiantesRiesgoDA/estadisticas', [EstudianteRiesgoController::class, 'obtenerEstadisticas']);
Route::get('estudiantesRiesgo/cursos', [EstudianteRiesgoController::class, 'obtenerCursos']);
Route::post('estudiantesRiesgo/comparacion-cursos', [EstudianteRiesgoController::class, 'compararCursos']);
Route::get('estudiantesRiesgoDA/ultimos-informes', [EstudianteRiesgoController::class, 'obtenerUltimosInformes']);

