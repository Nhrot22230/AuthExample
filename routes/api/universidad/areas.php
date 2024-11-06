<?php

use App\Http\Controllers\Universidad\AreaController;
use App\Http\Middleware\AuthzMiddleware;
use App\Models\Universidad\Area;
use Illuminate\Support\Facades\Route;

Route::get('areas', [AreaController::class, 'indexAll'])->middleware('can:ver areas');
Route::get('areas/paginated', [AreaController::class, 'index'])->middleware('can:ver areas');
Route::post('areas', [AreaController::class, 'store'])->middleware('can:manage areas');
Route::get('areas/{id}', [AreaController::class, 'show'])->middleware([AuthzMiddleware::class . ':ver areas,' . Area::class]);
Route::put('areas/{id}', [AreaController::class, 'update'])->middleware([AuthzMiddleware::class . ':manage areas,' . Area::class]);
Route::delete('areas/{id}', [AreaController::class, 'destroy'])->middleware([AuthzMiddleware::class . ':manage areas,' . Area::class]);
