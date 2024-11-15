<?php

use App\Http\Controllers\Usuarios\AdministrativoController;
use Illuminate\Support\Facades\Route;


Route::middleware("can:usuarios")->group(function () {
    Route::get('administrativos', [AdministrativoController::class, 'index']);
    Route::post('administrativos', [AdministrativoController::class, 'store']);
    Route::post('administrativos/multiple', [AdministrativoController::class, 'storeMultiple']);
    Route::get('administrativos/{codAdministrativo}', [AdministrativoController::class, 'show']);
    Route::put('administrativos/{codAdministrativo}', [AdministrativoController::class, 'update']);
    Route::delete('administrativos/{codAdministrativo}', [AdministrativoController::class, 'destroy']);
});