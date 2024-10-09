<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Usuarios\AdministrativoController;
use App\Http\Controllers\Usuarios\DocenteController;
use App\Http\Controllers\Usuarios\EstudianteController;
use App\Http\Controllers\Usuarios\UsuarioController;
use App\Http\Controllers\Usuarios\RolePermissionsController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JWTMiddleware;

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello World!',
    ]);
});

Route::middleware([JWTMiddleware::class, 'api'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->middleware('can:ver usuarios');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->middleware('can:crear usuarios');
        Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])->middleware('can:ver usuarios');
        Route::get('/usuarios/{id}/roles', [RolePermissionsController::class, 'listUserRoles'])->middleware('can:ver roles');
        Route::get('/usuarios/{id}/permissions', [RolePermissionsController::class, 'listUserPermissions'])->middleware('can:ver permisos');
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->middleware('can:editar usuarios');
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->middleware('can:eliminar usuarios');
    });

    Route::prefix('v1')->group(function () {
        Route::get('/estudiantes', [EstudianteController::class, 'index'])->middleware('can:ver estudiantes');
        Route::post('/estudiantes', [EstudianteController::class, 'store'])->middleware('can:crear estudiantes');
        Route::get('/estudiantes/{codEstudiante}', [EstudianteController::class, 'show'])->middleware('can:ver estudiantes');
        Route::put('/estudiantes/{codigo}', [EstudianteController::class, 'update'])->middleware('can:editar estudiantes');
        Route::delete('/estudiantes/{codigo}', [EstudianteController::class, 'destroy'])->middleware('can:eliminar estudiantes');
    });

    Route::prefix('v1')->group(function () {
        Route::get('/docentes', [DocenteController::class, 'index'])->middleware('can:ver docentes');
        Route::post('/docentes', [DocenteController::class, 'store'])->middleware('can:crear docentes');
        Route::get('/docentes/{codDocente}', [DocenteController::class, 'show'])->middleware('can:ver docentes');
        Route::put('/docentes/{codigo}', [DocenteController::class, 'update'])->middleware('can:editar docentes');
        Route::delete('/docentes/{codigo}', [DocenteController::class, 'destroy'])->middleware('can:eliminar docentes');
    });

    Route::prefix('v1')->group(function () {
        Route::get('/administrativos', [AdministrativoController::class, 'index'])->middleware('can:ver administrativos');
        Route::post('/administrativos', [AdministrativoController::class, 'store'])->middleware('can:crear administrativos');
        Route::get('/administrativos/{codigo}', [AdministrativoController::class, 'show'])->middleware('can:ver administrativos');
        Route::put('/administrativos/{codigo}', [AdministrativoController::class, 'update'])->middleware('can:editar administrativos');
        Route::delete('/administrativos/{codigo}', [AdministrativoController::class, 'destroy'])->middleware('can:eliminar administrativos');
    });

    Route::prefix('v1')->group(function () {
        Route::get('/roles', [RolePermissionsController::class, 'listRoles'])->middleware('can:ver roles');
        Route::post('/roles', [RolePermissionsController::class, 'storeRole'])->middleware('can:crear roles');
        Route::get('/roles/{id}', [RolePermissionsController::class, 'showRole'])->middleware('can:ver roles');
        Route::get('/roles/count', [RolePermissionsController::class, 'countRoles'])->middleware('can:ver roles');
        Route::post('/roles/assign', [RolePermissionsController::class, 'assignRole'])->middleware('can:asignar roles');
        Route::post('/roles/revoke', [RolePermissionsController::class, 'revokeRole'])->middleware('can:revocar roles');
        Route::post('/roles/delete', [RolePermissionsController::class, 'deleteRole'])->middleware('can:eliminar roles');

        Route::get('/permissions', [RolePermissionsController::class, 'listPermissions'])->middleware('can:ver permisos');
        Route::post('/permissions', [RolePermissionsController::class, 'storePermission'])->middleware('can:crear permisos');
        Route::post('/permissions/assign', [RolePermissionsController::class, 'assignPermission'])->middleware('can:asignar permisos');
        Route::post('/permissions/revoke', [RolePermissionsController::class, 'revokePermission'])->middleware('can:revocar permisos');
        Route::post('/permissions/delete', [RolePermissionsController::class, 'deletePermission'])->middleware('can:eliminar permisos');
    });
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login-google', [AuthController::class, 'googleLogin']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Route::middleware(JWTMiddleware::class)->group(function () {
//     Route::prefix('auth')->group(function () {
//         Route::get('/me', [AuthController::class, 'me']);
//         Route::get('/refresh', [AuthController::class, 'refresh']);
//         Route::get('/logout', [AuthController::class, 'logout']);
//     });
// });

Route::middleware(JWTMiddleware::class, 'api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });
});
