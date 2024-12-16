<?php

use App\Http\Controllers\GestionSeccion\GestionProfesoresJPsController;
use Illuminate\Support\Facades\Route;

Route::middleware("can:gestion-profesores-jps")->group(function () {
    Route::post('gestion-profesores-jps/{seccion_id}/asignar-docentes', [GestionProfesoresJPsController::class, 'asignarDocentes']);
    Route::post('gestion-profesores-jps/{seccion_id}/asignar-jps', [GestionProfesoresJPsController::class, 'asignarJefesPractica']);

});
