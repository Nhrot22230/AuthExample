<?php

use App\Http\Controllers\Usuarios\DocenteController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('docentes', [DocenteController::class, 'index'])->middleware('can:ver usuarios');
Route::post('docentes', [DocenteController::class, 'store'])->middleware('can:manage usuarios');
Route::post('docentes/multiple', [DocenteController::class, 'storeMultiple'])->middleware('can:manage usuarios');
Route::get('docentes/{codDocente}', [DocenteController::class, 'show'])->middleware('can:ver usuarios');
Route::put('docentes/{codDocente}', [DocenteController::class, 'update'])->middleware('can:manage usuarios');
Route::delete('docentes/{codDocente}', [DocenteController::class, 'destroy'])->middleware('can:manage usuarios');
