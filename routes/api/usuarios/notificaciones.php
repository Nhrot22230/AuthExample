<?php

use App\Http\Controllers\Usuarios\NotificationsController;
use Illuminate\Support\Facades\Route;


Route::post('notifications/notify', [NotificationsController::class, 'notifyToUsers']);
Route::get('notifications/my-notifications', [NotificationsController::class, 'notifications']);
Route::put('notifications/{id}', [NotificationsController::class, 'update']);
Route::delete('notifications/{id}', [NotificationsController::class, 'destroy']);
