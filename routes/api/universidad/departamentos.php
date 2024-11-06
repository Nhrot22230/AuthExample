<?php

use App\Http\Controllers\Universidad\DepartamentoController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Departamento;
use Illuminate\Support\Facades\Route;


Route::get('departamentos', [DepartamentoController::class, 'indexAll'])->middleware('can:ver departamentos');
Route::get('departamentos/paginated', [DepartamentoController::class, 'index'])->middleware('can:ver departamentos');
Route::post('departamentos', [DepartamentoController::class, 'store'])->middleware('can:manage departamentos');
Route::get('departamentos/{id}', [DepartamentoController::class, 'show'])->middleware([AuthzMiddleware::class . ':ver departamentos,' . Departamento::class]);
Route::put('departamentos/{id}', [DepartamentoController::class, 'update'])->middleware([AuthzMiddleware::class . ':manage departamentos,' . Departamento::class]);
Route::delete('departamentos/{id}', [DepartamentoController::class, 'destroy'])->middleware([AuthzMiddleware::class . ':manage departamentos,' . Departamento::class]);
Route::get('departamentos/nombre/{nombre}', [DepartamentoController::class, 'showByName'])->middleware('can:ver departamentos');
