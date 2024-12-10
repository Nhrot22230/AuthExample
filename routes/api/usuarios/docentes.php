<?php

use App\Http\Controllers\Usuarios\DocenteController;
use Illuminate\Support\Facades\Route;

Route::middleware("can:usuarios")->group(function () {
    //Route::get('docentes', [DocenteController::class, 'index']);
    Route::post('docentes', [DocenteController::class, 'store']);
    Route::post('docentes/multiple', [DocenteController::class, 'storeMultiple']);
    Route::get('docentes/{codDocente}', [DocenteController::class, 'show']);
    Route::put('docentes/{codDocente}', [DocenteController::class, 'update']);
    Route::delete('docentes/{codDocente}', [DocenteController::class, 'destroy']); 
});

Route::get('docentes', [DocenteController::class, 'index']);