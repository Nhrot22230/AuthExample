<?php

use App\Http\Controllers\Tramites\TemaDeTesisController;
use Illuminate\Support\Facades\Route;


Route::get('temas-de-tesis', [TemaDeTesisController::class, 'indexPaginated']);
Route::get('temas-de-tesis/{id}', [TemaDeTesisController::class, 'show']);
Route::put('temas-de-tesis/{id}', [TemaDeTesisController::class, 'update']);
