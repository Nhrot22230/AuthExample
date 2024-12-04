<?php

use App\Http\Controllers\GestionEspecialidad\GestionAlumnosController;
use Illuminate\Support\Facades\Route;

Route::middleware("can:gestion-alumnos")->group(function () {
    Route::post('gestion-alumnos/{especialidad_id}/asignar-alumnos', [GestionAlumnosController::class, 'asignarAlumnos']);

});
