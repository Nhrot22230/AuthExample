<?php

use App\Http\Controllers\Usuarios\AdministrativoController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;


Route::middleware([JWTMiddleware::class, 'api'])->group(
    function (): void {
        Route::prefix( 'v1')->group(function (): void {
            Route::get('/administrativos', [AdministrativoController::class, 'index'])->middleware('can:ver usuarios');
            Route::post('/administrativos', [AdministrativoController::class, 'store'])->middleware('can:manage usuarios');
            Route::post('/administrativos/multiple', [AdministrativoController::class, 'storeMultiple'])->middleware('can:manage usuarios');
            Route::get('/administrativos/{codAdministrativo}', [AdministrativoController::class, 'show'])->middleware('can:ver usuarios');
            Route::put('/administrativos/{codAdministrativo}', [AdministrativoController::class, 'update'])->middleware('can:manage usuarios');
            Route::delete('/administrativos/{codAdministrativo}', [AdministrativoController::class, 'destroy'])->middleware('can:manage usuarios');
        });
    }
);