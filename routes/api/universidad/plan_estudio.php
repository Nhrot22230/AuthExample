<?php

use App\Models\Universidad\Especialidad;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Universidad\PlanEstudioController;
use App\Http\Middleware\AuthzMiddleware;

Route::prefix('especialidades/{entity_id}/plan-estudio')
    ->middleware(AuthzMiddleware::class . ':especialidades,' . Especialidad::class)
    ->group(function () {
        Route::get('/', [PlanEstudioController::class, 'index']);
        Route::post('/', [PlanEstudioController::class, 'store']);
        Route::get('/paginated', [PlanEstudioController::class, 'indexPaginated']);
        Route::get('/current', [PlanEstudioController::class, 'currentByEspecialidad']);
        Route::put('/{plan_id}', [PlanEstudioController::class, 'update']);
        Route::get('/{plan_id}', [PlanEstudioController::class, 'show']);
        Route::delete('/{plan_id}', [PlanEstudioController::class, 'destroy']);
    });
