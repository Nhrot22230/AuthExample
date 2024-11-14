<?php

use App\Http\Controllers\Authorization\RolePermissionsController;
use Illuminate\Support\Facades\Route;

Route::group([
    "middleware" => [
        "can:autorizacion"
    ]
], function () {
    Route::get('usuarios/{id}/roles', [RolePermissionsController::class, 'listUserRoles']);
    Route::get('scopes', [RolePermissionsController::class, 'indexScopes']);
    Route::get('roles-scopes', [RolePermissionsController::class, 'indexRolesScopes']);
    Route::get('roles', [RolePermissionsController::class, 'indexRoles']);
    Route::get('roles/{id}', [RolePermissionsController::class, 'showRole']);
    Route::get('permissions', [RolePermissionsController::class, 'indexPermissions']);
    Route::put('roles/{id}', [RolePermissionsController::class, 'updateRole']);
    Route::post('roles', [RolePermissionsController::class, 'storeRole']);
    Route::post('usuarios/{id}/sync-roles', [RolePermissionsController::class, 'syncRoles']);
    Route::delete('roles/{id}', [RolePermissionsController::class, 'destroyRole']);
});

Route::get('permissions/my-permissions', [RolePermissionsController::class, 'authUserPermissions']);
Route::get('roles/auth/my-roles', [RolePermissionsController::class, 'authUserRoles']);

