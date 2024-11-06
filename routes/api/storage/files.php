<?php

use App\Http\Controllers\Storage\FileController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware([JWTMiddleware::class, 'api'])->group(
    function (): void {
        Route::prefix('v1')->group(function (): void {
            Route::post('/files/upload', [FileController::class, 'uploadFile']);
            Route::get('/files/{filename}', [FileController::class, 'download']);
            Route::get('/files', [FileController::class, 'listFiles']);
            Route::delete('/files/{filename}', [FileController::class, 'deleteFile']);
            Route::get('/files/{filename}/metadata', [FileController::class, 'getFileMetadata']);
        });
    }
);