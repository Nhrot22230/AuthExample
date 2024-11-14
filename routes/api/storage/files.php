<?php

use App\Http\Controllers\Storage\FileController;
use Illuminate\Support\Facades\Route;


Route::post('files/upload', [FileController::class, 'uploadFile']);
Route::get('files/{filename}', [FileController::class, 'download']);
Route::get('files/{filename}/metadata', [FileController::class, 'getFileMetadata']);
Route::get('files/id/{id}', [FileController::class, 'downloadById']);
Route::put('files/id/{id}', [FileController::class, 'updateFile']);
Route::delete('files/{filename}', [FileController::class, 'deleteFile']);
