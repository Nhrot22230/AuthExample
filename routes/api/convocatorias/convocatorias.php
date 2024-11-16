<?php

use App\Http\Controllers\Convocatorias\ConvocatoriaController;
use Illuminate\Support\Facades\Route;

Route::get('/convocatorias/index', [ConvocatoriaController::class, 'index']);
Route::get('/convocatorias', [ConvocatoriaController::class, 'listar_convocatorias_todas']);
Route::get('/convocatorias/criterios/{entity_id}', [ConvocatoriaController::class, 'indexCriterios']);
Route::post('/convocatorias', [ConvocatoriaController::class, 'store']);
Route::get('/convocatorias/{id}', [ConvocatoriaController::class, 'show']);
Route::put('/convocatorias/{id}', [ConvocatoriaController::class, 'update']);
