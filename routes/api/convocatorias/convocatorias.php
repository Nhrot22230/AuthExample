<?php

use App\Http\Controllers\Convocatorias\ConvocatoriaController;
use Illuminate\Support\Facades\Route;

Route::get('/convocatorias/index', [ConvocatoriaController::class, 'index']);
Route::get('/convocatorias', [ConvocatoriaController::class, 'listarConvocatoriasTodas']);
Route::get('/convocatorias/criterios/{entity_id}', [ConvocatoriaController::class, 'indexCriterios']);
Route::post('/convocatorias', [ConvocatoriaController::class, 'store']);
Route::get('/convocatorias/{id}', [ConvocatoriaController::class, 'show']);
Route::put('/convocatorias/{id}', [ConvocatoriaController::class, 'update']);
Route::post('/convocatorias/criterios', [ConvocatoriaController::class, 'storeGrupoCriterios']);
Route::put('/convocatorias/criterios/{id}', [ConvocatoriaController::class, 'updateGrupoCriterios']);
Route::get('/convocatorias/{id}/candidatos', [ConvocatoriaController::class, 'getCandidatosByConvocatoria']);
Route::post('/convocatorias/{id}/postular', [ConvocatoriaController::class, 'postularConvocatoria']);
Route::get('/convocatorias/{idConvocatoria}/{idCandidato}', [ConvocatoriaController::class, 'obtenerEstadoCandidato']);
Route::put('/convocatorias/{idConvocatoria}/{idCandidato}', [ConvocatoriaController::class, 'actualizarEstado']);
