<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tramites\CartaPresentacionController;

Route::prefix('cartas')->group(function () {
    Route::post('/{idEstudiante}/filtrar', [CartaPresentacionController::class, 'index']);
    Route::get('/crear/{idEstudiante}', [CartaPresentacionController::class, 'create']);
    Route::post('/{idEstudiante}', [CartaPresentacionController::class, 'store']);
});
