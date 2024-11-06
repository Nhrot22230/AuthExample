<?php

use App\Http\Controllers\Universidad\SeccionController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Seccion;
use Illuminate\Support\Facades\Route;


Route::get('secciones', [SeccionController::class, 'indexAll'])->middleware('can:ver secciones');
Route::get('secciones/paginated', [SeccionController::class, 'index'])->middleware('can:ver secciones');
Route::post('secciones', [SeccionController::class, 'store'])->middleware('can:manage secciones');
Route::get('secciones/{id}', [SeccionController::class, 'show'])->middleware([AuthzMiddleware::class . ':ver secciones,' . Seccion::class]);
Route::put('secciones/{id}', [SeccionController::class, 'update'])->middleware([AuthzMiddleware::class . ':manage secciones,' . Seccion::class]);
Route::delete('secciones/{id}', [SeccionController::class, 'destroy'])->middleware([AuthzMiddleware::class . ':manage secciones,' . Seccion::class]);
