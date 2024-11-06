<?php

use App\Http\Controllers\Universidad\PlanEstudioController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Especialidad;
use Illuminate\Support\Facades\Route;


Route::get('plan-estudio', [PlanEstudioController::class, 'index'])->middleware('can:ver planes de estudio');
Route::get('plan-estudio/paginated', [PlanEstudioController::class, 'indexPaginated'])->middleware('can:ver planes de estudio');
Route::get('plan-estudio/current/{id}', [PlanEstudioController::class, 'currentByEspecialidad'])->middleware([AuthzMiddleware::class . ':ver planes de estudio,' . Especialidad::class]);
Route::post('plan-estudio', [PlanEstudioController::class, 'store'])->middleware('can:manage planes de estudio');
Route::put('plan-estudio/{id}', [PlanEstudioController::class, 'update'])->middleware('can:manage planes de estudio');
Route::get('plan-estudio/{id}', [PlanEstudioController::class, 'show'])->middleware('can:ver planes de estudio');
Route::delete('plan-estudio/{id}', [PlanEstudioController::class, 'destroy'])->middleware('can:manage planes de estudio');
