<?php

use App\Http\Controllers\PedidoCursosController;
use Illuminate\Support\Facades\Route;


Route::get('pedidos-cursos', [PedidoCursosController::class, 'index']);
Route::patch('/pedidos-cursos/enviar-multiples', [PedidoCursosController::class, 'enviarMultiplesPedidos']);
Route::get('facultades/{facultad}/pedidos-cursos', [PedidoCursosController::class, 'getByFacultad']);
Route::get('/especialidades/{especialidad}/cursos-pedido', [PedidoCursosController::class, 'getCursosPorEspecialidad']);