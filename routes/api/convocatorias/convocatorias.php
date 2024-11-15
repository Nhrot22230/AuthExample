<?php

use App\Http\Controllers\Convocatorias\ConvocatoriaController;
use Illuminate\Support\Facades\Route;

Route::get('/convocatorias/index', [ConvocatoriaController::class, 'index']);
Route::get('/convocatorias', [ConvocatoriaController::class, 'listar_convocatorias_todas']);
