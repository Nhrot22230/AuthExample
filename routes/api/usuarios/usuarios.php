<?php

use App\Http\Controllers\Usuarios\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('usuarios', [UsuarioController::class, 'index'])->middleware('can:ver usuarios');
Route::post('usuarios', [UsuarioController::class, 'store'])->middleware('can:manage usuarios');
Route::get('usuarios/{id}', [UsuarioController::class, 'show'])->middleware('can:ver usuarios');
Route::put('usuarios/{id}', [UsuarioController::class, 'update'])->middleware('can:manage usuarios');
Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy'])->middleware('can:manage usuarios');
