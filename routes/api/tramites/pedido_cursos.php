<?php

use App\Http\Controllers\Tramites\PedidoCursosController;
use Illuminate\Support\Facades\Route;


Route::get('pedidos-cursos', [PedidoCursosController::class, 'index']);
Route::patch('/pedidos-cursos/enviar-multiples', [PedidoCursosController::class, 'enviarMultiplesPedidos']);
Route::get('facultades/{facultad}/pedidos-cursos', [PedidoCursosController::class, 'getByFacultad']);
Route::get('/especialidades/{especialidad}/cursos-pedido', [PedidoCursosController::class, 'getCursosPorEspecialidad']);
Route::delete('horarios/{id}', [PedidoCursosController::class, 'destroyHorario']);
Route::delete('/horarios', [PedidoCursosController::class, 'destroyMultipleHorarios']);
Route::post('/horarios', [PedidoCursosController::class, 'createHorarios']);
Route::delete('/pedidos/{pedidoId}/cursos-electivos', [PedidoCursosController::class, 'removeCursosElectivos']);
Route::get('/especialidades/{especialidadId}/cursos-electivos', [PedidoCursosController::class, 'getCursosElectivosPorEspecialidad']);
Route::post('/pedidos/{pedidoId}/cursos-electivos', [PedidoCursosController::class, 'addCursosElectivosToPedido']);
Route::patch('/pedidos/{pedidoId}/recibir', [PedidoCursosController::class, 'markPedidoAsReceived']);
Route::patch('/pedidos/{pedidoId}/estado', [PedidoCursosController::class, 'updatePedidoStatus']);
