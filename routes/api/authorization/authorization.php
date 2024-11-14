<?php

use App\Http\Controllers\Authorization\RolePermissionsController;
use Illuminate\Support\Facades\Route;


Route::get('usuarios/{id}/roles', [RolePermissionsController::class, 'listUserRoles'])->middleware('can:ver roles');
Route::get('usuarios/{id}/permissions', [RolePermissionsController::class, 'listUserPermissions'])->middleware('can:ver permisos');
Route::get('scopes', [RolePermissionsController::class, 'indexScopes'])->middleware('can:ver roles');
Route::get('roles-scopes', [RolePermissionsController::class, 'indexRolesScopes'])->middleware('can:ver roles');
Route::get('roles', [RolePermissionsController::class, 'indexRoles'])->middleware('can:ver roles');
Route::get('roles/{id}', [RolePermissionsController::class, 'showRole'])->middleware('can:ver roles');
Route::post('roles', [RolePermissionsController::class, 'storeRole'])->middleware('can:manage roles');
Route::put('roles/{id}', [RolePermissionsController::class, 'updateRole'])->middleware('can:manage roles');
Route::delete('roles/{id}', [RolePermissionsController::class, 'destroyRole'])->middleware('can:manage roles');
Route::get('permissions', [RolePermissionsController::class, 'indexPermissions'])->middleware('can:ver permisos');
Route::get('permissions/my-permissions', [RolePermissionsController::class, 'authUserPermissions']);
Route::get('roles/auth/my-roles', [RolePermissionsController::class, 'authUserRoles']);
Route::post('usuarios/{id}/sync-roles', [RolePermissionsController::class, 'syncRoles'])->middleware('can:manage roles');
Route::post('usuarios/{id}/sync-permissions', [RolePermissionsController::class, 'syncPermissions'])->middleware('can:manage roles');
