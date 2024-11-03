<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\PlanEstudioController;
use App\Http\Controllers\Universidad\AreaController;
use App\Http\Controllers\Universidad\CursoController;
use App\Http\Controllers\Universidad\DepartamentoController;
use App\Http\Controllers\Universidad\EspecialidadController;
use App\Http\Controllers\Universidad\FacultadController;
use App\Http\Controllers\Universidad\SeccionController;
use App\Http\Controllers\Universidad\SemestreController;
use App\Http\Controllers\Usuarios\AdministrativoController;
use App\Http\Controllers\Usuarios\DocenteController;
use App\Http\Controllers\Usuarios\EstudianteController;
use App\Http\Controllers\Usuarios\UsuarioController;
use App\Http\Controllers\Usuarios\RolePermissionsController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JWTMiddleware;
use App\Http\Controllers\EstudianteRiesgoController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello World!',
    ]);
});

Route::middleware([JWTMiddleware::class, 'api'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('/instituciones', [InstitucionController::class, 'listConfiguraciones'])->middleware('can:ver instituciones');
        Route::get('/instituciones/last', [InstitucionController::class, 'getLastConfiguracion'])->middleware('can:ver instituciones');
        Route::post('/instituciones', [InstitucionController::class, 'setConfiguracion'])->middleware('can:manage instituciones');


        Route::get('/departamentos', [DepartamentoController::class, 'indexAll'])->middleware('can:ver departamentos');
        Route::get('/departamentos/paginated', [DepartamentoController::class, 'index'])->middleware('can:ver departamentos');
        Route::post('/departamentos', [DepartamentoController::class, 'store'])->middleware('can:manage departamentos');
        Route::get('/departamentos/{id}', [DepartamentoController::class, 'show'])->middleware('can:ver departamentos');
        Route::put('/departamentos/{id}', [DepartamentoController::class, 'update'])->middleware('can:manage departamentos');
        Route::delete('/departamentos/{id}', [DepartamentoController::class, 'destroy'])->middleware('can:manage departamentos');
        Route::get('/departamentos/nombre/{nombre}', [DepartamentoController::class, 'showByName'])->middleware('can:ver departamentos');


        Route::get('/facultades', [FacultadController::class, 'indexAll'])->middleware('can:ver facultades');
        Route::get('/facultades/paginated', [FacultadController::class, 'index'])->middleware('can:ver facultades');
        Route::post('/facultades', [FacultadController::class, 'store'])->middleware('can:manage facultades');
        Route::get('/facultades/{id}', [FacultadController::class, 'show'])->middleware('can:ver facultades');
        Route::put('/facultades/{id}', [FacultadController::class, 'update'])->middleware('can:manage facultades');
        Route::delete('/facultades/{id}', [FacultadController::class, 'destroy'])->middleware('can:manage facultades');
        Route::get('/facultades/nombre/{nombre}', [FacultadController::class, 'showByName'])->middleware('can:ver facultades');


        Route::get('/areas', [AreaController::class, 'indexAll'])->middleware('can:ver areas');
        Route::get('/areas/paginated', [AreaController::class, 'index'])->middleware('can:ver areas');
        Route::post('/areas', [AreaController::class, 'store'])->middleware('can:manage areas');
        Route::get('/areas/{id}', [AreaController::class, 'show'])->middleware('can:ver areas');
        Route::put('/areas/{id}', [AreaController::class, 'update'])->middleware('can:manage areas');
        Route::delete('/areas/{id}', [AreaController::class, 'destroy'])->middleware('can:manage areas');


        Route::get('/especialidades', [EspecialidadController::class, 'indexAll'])->middleware('can:ver especialidades');
        Route::get('/especialidades/paginated', [EspecialidadController::class, 'index'])->middleware('can:ver especialidades');
        Route::post('/especialidades', [EspecialidadController::class, 'store'])->middleware('can:manage especialidades');
        Route::get('/especialidades/{id}', [EspecialidadController::class, 'show'])->middleware('can:ver especialidades');
        Route::put('/especialidades/{id}', [EspecialidadController::class, 'update'])->middleware('can:manage especialidades');
        Route::delete('/especialidades/{id}', [EspecialidadController::class, 'destroy'])->middleware('can:manage especialidades');
        Route::get('/especialidades/nombre/{nombre}', [EspecialidadController::class, 'showByName'])->middleware('can:ver especialidades');


        Route::get('/secciones', [SeccionController::class, 'indexAll'])->middleware('can:ver secciones');
        Route::get('/secciones/paginated', [SeccionController::class, 'index'])->middleware('can:ver secciones');
        Route::post('/secciones', [SeccionController::class, 'store'])->middleware('can:manage secciones');
        Route::get('/secciones/{id}', [SeccionController::class, 'show'])->middleware('can:ver secciones');
        Route::put('/secciones/{id}', [SeccionController::class, 'update'])->middleware('can:manage secciones');
        Route::delete('/secciones/{id}', [SeccionController::class, 'destroy'])->middleware('can:manage secciones');


        Route::get('/cursos', [CursoController::class, 'index'])->middleware('can:ver cursos');
        Route::get('/cursos/paginated', [CursoController::class, 'indexPaginated'])->middleware('can:ver cursos');
        Route::get('/cursos/{codigo}', [CursoController::class, 'getByCodigo'])->middleware('can:ver cursos');
        Route::post('/cursos', [CursoController::class, 'store'])->middleware('can:manage cursos');
        Route::get('/cursos/{id}', [CursoController::class, 'show'])->middleware('can:ver cursos');
        Route::put('/cursos/{id}', [CursoController::class, 'update'])->middleware('can:manage cursos');
        Route::delete('/cursos/{id}', [CursoController::class, 'destroy'])->middleware('can:manage cursos');


        Route::get ('/plan-estudio', [PlanEstudioController::class, 'index'])->middleware('can:ver planes de estudio');
        Route::get ('/plan-estudio/paginated', [PlanEstudioController::class, 'indexPaginated'])->middleware('can:ver planes de estudio');
        Route::get ('/plan-estudio/current/{especialidad_id}', [PlanEstudioController::class, 'currentByEspecialidad'])->middleware('can:ver planes de estudio');
        Route::post('/plan-estudio', [PlanEstudioController::class, 'store'])->middleware('can:manage planes de estudio');
        Route::put ('/plan-estudio/{id}', [PlanEstudioController::class, 'update'])->middleware('can:manage planes de estudio');
        Route::get ('/plan-estudio/{id}', [PlanEstudioController::class, 'show'])->middleware('can:ver planes de estudio');
        Route::delete ('/plan-estudio/{id}', [PlanEstudioController::class, 'destroy'])->middleware('can:manage planes de estudio');


        Route::get('/semestres', [SemestreController::class, 'indexAll'])->middleware('can:ver semestres');
        Route::get('/semestres/paginated', [SemestreController::class, 'index'])->middleware('can:ver semestres');
        Route::get('/semestres/last', [SemestreController::class, 'getLastSemestre'])->middleware('can:ver semestres');
        Route::post('/semestres', [SemestreController::class, 'store'])->middleware('can:manage semestres');
        Route::delete('/semestres/eliminarSemestres', [SemestreController::class, 'destroyMultiple']);
        Route::get('/semestres/{id}', [SemestreController::class, 'show'])->middleware('can:ver semestres');
        Route::put('/semestres/{id}', [SemestreController::class, 'update'])->middleware('can:manage semestres');
        Route::delete('/semestres/{id}', [SemestreController::class, 'destroy'])->middleware('can:manage semestres');


        Route::get('/usuarios', [UsuarioController::class, 'index'])->middleware('can:ver usuarios');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->middleware('can:manage usuarios');
        Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])->middleware('can:ver usuarios');
        Route::get('/usuarios/{id}/roles', [RolePermissionsController::class, 'listUserRoles'])->middleware('can:ver roles');
        Route::get('/usuarios/{id}/permissions', [RolePermissionsController::class, 'listUserPermissions'])->middleware('can:ver permisos');
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->middleware('can:manage usuarios');
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->middleware('can:manage usuarios');


        Route::get('/estudiantes', [EstudianteController::class, 'index'])->middleware('can:ver estudiantes');
        Route::post('/estudiantes', [EstudianteController::class, 'store'])->middleware('can:manage estudiantes');
        Route::post('/estudiantes/multiple', [EstudianteController::class, 'storeMultiple'])->middleware('can:manage estudiantes');
        Route::get('/estudiantes/{codEstudiante}', [EstudianteController::class, 'show'])->middleware('can:ver estudiantes');
        Route::put('/estudiantes/{codEstudiante}', [EstudianteController::class, 'update'])->middleware('can:manage estudiantes');
        Route::delete('/estudiantes/{codEstudiante}', [EstudianteController::class, 'destroy'])->middleware('can:manage estudiantes');


        Route::get('/docentes', [DocenteController::class, 'index'])->middleware('can:ver docentes');
        Route::post('/docentes', [DocenteController::class, 'store'])->middleware('can:manage docentes');
        Route::post('/docentes/multiple', [DocenteController::class, 'storeMultiple'])->middleware('can:manage docentes');
        Route::get('/docentes/{codDocente}', [DocenteController::class, 'show'])->middleware('can:ver docentes');
        Route::put('/docentes/{codDocente}', [DocenteController::class, 'update'])->middleware('can:manage docentes');
        Route::delete('/docentes/{codDocente}', [DocenteController::class, 'destroy'])->middleware('can:manage docentes');


        Route::get('/administrativos', [AdministrativoController::class, 'index'])->middleware('can:ver administrativos');
        Route::post('/administrativos', [AdministrativoController::class, 'store'])->middleware('can:manage administrativos');
        Route::post('/administrativos/multiple', [AdministrativoController::class, 'storeMultiple'])->middleware('can:manage administrativos');
        Route::get('/administrativos/{codAdministrativo}', [AdministrativoController::class, 'show'])->middleware('can:ver administrativos');
        Route::put('/administrativos/{codAdministrativo}', [AdministrativoController::class, 'update'])->middleware('can:manage administrativos');
        Route::delete('/administrativos/{codAdministrativo}', [AdministrativoController::class, 'destroy'])->middleware('can:manage administrativos');


        Route::post('/usuarios/sync-roles', [RolePermissionsController::class, 'syncRoles'])->middleware('can:manage roles');
        Route::post('/usuarios/sync-permissions', [RolePermissionsController::class, 'syncPermissions'])->middleware('can:manage permisos');


        Route::get('/roles', [RolePermissionsController::class, 'indexRoles'])->middleware('can:ver roles');
        Route::get('/roles/paginated', [RolePermissionsController::class, 'indexRolesPaginated'])->middleware('can:ver roles');
        Route::post('/roles', [RolePermissionsController::class, 'storeRole'])->middleware('can:manage roles');
        Route::get('/roles/{id}', [RolePermissionsController::class, 'showRole'])->middleware('can:ver roles');
        Route::put('/roles/{id}', [RolePermissionsController::class, 'updateRole'])->middleware('can:manage roles');
        Route::delete('/roles/{id}', [RolePermissionsController::class, 'destroyRole'])->middleware('can:manage roles');


        Route::get('/permissions', [RolePermissionsController::class, 'indexPermissions'])->middleware('can:ver permisos');
        Route::get('/permissions/paginated', [RolePermissionsController::class, 'indexPermissionsPaginated'])->middleware('can:ver permisos');
        Route::get('/permissions/{id}', [RolePermissionsController::class, 'showPermission'])->middleware('can:ver permisos');
        Route::put('/permissions/{id}', [RolePermissionsController::class, 'updatePermission'])->middleware('can:manage permisos');

        Route::get('/unidades/mine', [AuthController::class, 'getMyUnidades']);

        Route::get('/estudiantesRiesgo/listar_profesor', [EstudianteRiesgoController::class, 'listar_por_especialidad_profesor']);
        Route::get('/estudiantesRiesgo/listar_director', [EstudianteRiesgoController::class, 'listar_por_especialidad_director']);
        Route::get('/estudiantesRiesgo/listar_informes', [EstudianteRiesgoController::class, 'listar_informes_estudiante']);
        Route::put('/estudiantesRiesgo/actualizar_informe', [EstudianteRiesgoController::class, 'actualizar_informe_estudiante']);
        Route::post('/estudiantesRiesgo/carga_alumnos', [EstudianteRiesgoController::class, 'carga_alumnos_riesgo']);
        Route::post('/estudiantesRiesgo/agregar_informe', [EstudianteRiesgoController::class, 'agregar_informe_estudiante']);
    });
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login-google', [AuthController::class, 'googleLogin']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(JWTMiddleware::class, 'api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/me', [AuthController::class, 'me']);
    });
});


use App\Http\Controllers\ImageController;

Route::prefix('v1')->group(function () {

    Route::post('/images/upload', [ImageController::class, 'upload']);
    Route::get('/images/{filename}', [ImageController::class, 'getMIME']);
});


Route::get('/encuesta-docente', [EncuestaController::class, 'indexEncuestaDocente']);


Route::get('/encuesta-jefe-practica', [EncuestaController::class, 'indexEncuestaJefePractica']);

