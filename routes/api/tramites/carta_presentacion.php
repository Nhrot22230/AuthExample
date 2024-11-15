<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tramites\CartaPresentacionController;

Route::prefix('cartas')->group(function () {
    Route::post('/{idEstudiante}/filtrar-estudiante', [CartaPresentacionController::class, 'index']);
    Route::post('/{idDocente}/filtrar-docente', [CartaPresentacionController::class, 'indexDocente']);
    Route::post('/{idUsuario}/filtrar-director', [CartaPresentacionController::class, 'indexDirector']);
    Route::post('/{idUsuario}/filtrar-secretaria', [CartaPresentacionController::class, 'indexSecretaria']);
    Route::get('/crear/{idEstudiante}', [CartaPresentacionController::class, 'create']);
    Route::post('/{idEstudiante}', [CartaPresentacionController::class, 'store']);
});
