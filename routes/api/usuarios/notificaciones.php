<?php

use App\Http\Controllers\Usuarios\NotificationsController;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Support\Facades\Route;


Route::middleware([JWTMiddleware::class, 'api'])->group(
    function () {
        Route::prefix('v1')->group(function () {
            Route::post('/notifications/notify', [NotificationsController::class, 'notifyToUsers']);
            Route::get('/notifications/my-notifications', [NotificationsController::class, 'notifications']);
            Route::put('/notifications/{id}', [NotificationsController::class, 'update']);
            Route::delete('/notifications/{id}', [NotificationsController::class, 'destroy']);
        });
    }
);