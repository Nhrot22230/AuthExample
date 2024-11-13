<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\InstitucionController;
use App\Http\Controllers\PlanEstudioController;
use App\Http\Controllers\TemaDeTesisController;
use App\Http\Controllers\HorarioController;
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
use App\Http\Controllers\MatriculaAdicionalController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PedidoCursosController;

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello World!',
    ]);
});

Route::middleware([JWTMiddleware::class, 'api'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('/instituciones', [InstitucionController::class, 'listConfiguraciones'])->middleware('can:ver instituciones');
        Route::get('/instituciones/last', [InstitucionController::class, 'getLastConfiguracion'])->middleware('can:ver instituciones');
        Route::post('/instituciones', [InstitucionController::class, 'setConfiguracion'])->middleware('can:crear instituciones');


        Route::get('/departamentos', [DepartamentoController::class, 'indexAll'])->middleware('can:ver departamentos');
        Route::get('/departamentos/paginated', [DepartamentoController::class, 'index'])->middleware('can:ver departamentos');
        Route::post('/departamentos', [DepartamentoController::class, 'store'])->middleware('can:crear departamentos');
        Route::get('/departamentos/{id}', [DepartamentoController::class, 'show'])->middleware('can:ver departamentos');
        Route::put('/departamentos/{id}', [DepartamentoController::class, 'update'])->middleware('can:editar departamentos');
        Route::delete('/departamentos/{id}', [DepartamentoController::class, 'destroy'])->middleware('can:eliminar departamentos');
        Route::get('/departamentos/nombre/{nombre}', [DepartamentoController::class, 'showByName'])->middleware('can:ver departamentos');


        Route::get('/facultades', [FacultadController::class, 'indexAll'])->middleware('can:ver facultades');
        Route::get('/facultades/paginated', [FacultadController::class, 'index'])->middleware('can:ver facultades');
        Route::post('/facultades', [FacultadController::class, 'store'])->middleware('can:crear facultades');
        Route::get('/facultades/{id}', [FacultadController::class, 'show'])->middleware('can:ver facultades');
        Route::put('/facultades/{id}', [FacultadController::class, 'update'])->middleware('can:editar facultades');
        Route::delete('/facultades/{id}', [FacultadController::class, 'destroy'])->middleware('can:eliminar facultades');
        Route::get('/facultades/nombre/{nombre}', [FacultadController::class, 'showByName'])->middleware('can:ver facultades');
        Route::get('/matriculas-adicionales/facultad/{facultadId}', [MatriculaAdicionalController::class, 'getByFacultad'])->middleware('can:ver matriculas_facultad');


        Route::get('/areas', [AreaController::class, 'indexAll'])->middleware('can:ver areas');
        Route::get('/areas/paginated', [AreaController::class, 'index'])->middleware('can:ver areas');
        Route::post('/areas', [AreaController::class, 'store'])->middleware('can:crear areas');
        Route::get('/areas/{id}', [AreaController::class, 'show'])->middleware('can:ver areas');
        Route::put('/areas/{id}', [AreaController::class, 'update'])->middleware('can:editar areas');
        Route::delete('/areas/{id}', [AreaController::class, 'destroy'])->middleware('can:eliminar areas');


        Route::get('/especialidades', [EspecialidadController::class, 'indexAll'])->middleware('can:ver especialidades');
        Route::get('/especialidades/paginated', [EspecialidadController::class, 'index'])->middleware('can:ver especialidades');
        Route::post('/especialidades', [EspecialidadController::class, 'store'])->middleware('can:crear especialidades');
        Route::get('/especialidades/{id}', [EspecialidadController::class, 'show'])->middleware('can:ver especialidades');
        Route::put('/especialidades/{id}', [EspecialidadController::class, 'update'])->middleware('can:editar especialidades');
        Route::delete('/especialidades/{id}', [EspecialidadController::class, 'destroy'])->middleware('can:eliminar especialidades');
        Route::get('/especialidades/nombre/{nombre}', [EspecialidadController::class, 'showByName'])->middleware('can:ver especialidades');


        Route::get('/secciones', [SeccionController::class, 'indexAll'])->middleware('can:ver secciones');
        Route::get('/secciones/paginated', [SeccionController::class, 'index'])->middleware('can:ver secciones');
        Route::post('/secciones', [SeccionController::class, 'store'])->middleware('can:crear secciones');
        Route::get('/secciones/{id}', [SeccionController::class, 'show'])->middleware('can:ver secciones');
        Route::put('/secciones/{id}', [SeccionController::class, 'update'])->middleware('can:editar secciones');
        Route::delete('/secciones/{id}', [SeccionController::class, 'destroy'])->middleware('can:eliminar secciones');


        Route::get('/cursos', [CursoController::class, 'index'])->middleware('can:ver cursos');
        Route::get('/cursos/paginated', [CursoController::class, 'indexPaginated'])->middleware('can:ver cursos');
        Route::get('/cursos/{codigo}', [CursoController::class, 'getByCodigo'])->middleware('can:ver cursos');
        Route::post('/cursos', [CursoController::class, 'store'])->middleware('can:crear cursos');
        Route::get('/cursos/{id}', [CursoController::class, 'show'])->middleware('can:ver cursos');
        Route::put('/cursos/{id}', [CursoController::class, 'update'])->middleware('can:editar cursos');
        Route::delete('/cursos/{id}', [CursoController::class, 'destroy'])->middleware('can:eliminar cursos');


        Route::get ('/plan-estudio', [PlanEstudioController::class, 'index'])->middleware('can:ver planes de estudio');
        Route::get ('/plan-estudio/paginated', [PlanEstudioController::class, 'indexPaginated'])->middleware('can:ver planes de estudio');
        Route::get ('/plan-estudio/current/{especialidad_id}', [PlanEstudioController::class, 'currentByEspecialidad'])->middleware('can:ver planes de estudio');
        Route::post('/plan-estudio', [PlanEstudioController::class, 'store'])->middleware('can:crear planes de estudio');
        Route::put ('/plan-estudio/{id}', [PlanEstudioController::class, 'update'])->middleware('can:editar planes de estudio');
        Route::get ('/plan-estudio/{id}', [PlanEstudioController::class, 'show'])->middleware('can:ver planes de estudio');
        Route::delete ('/plan-estudio/{id}', [PlanEstudioController::class, 'destroy'])->middleware('can:crear planes de estudio');


        Route::post('/matriculas-adicionales', [MatriculaAdicionalController::class, 'store'])->middleware('can:crear matriculas_adicionales');
        //Route::get('/matriculas-adicionales', [MatriculaAdicionalController::class, 'getAll'])->middleware('can:ver matriculas_adicionales');
        Route::get('/matriculas-adicionales/{id}', [MatriculaAdicionalController::class, 'getByEspecialidad']) ->middleware('can:ver matriculas_especialidad');
        Route::get('/matriculas-adicionales/estudiante/{estudianteId}', [MatriculaAdicionalController::class, 'getByEstudiante'])->middleware('can:ver mis matriculas_adicionales'); // Puedes eliminar el middleware si no deseas autorización
        Route::get('/horarios/cursos/{cursoId}', [MatriculaAdicionalController::class, 'getHorariosByCurso'])->middleware('can:ver horarios');
        Route::get('/matricula-adicional/{id}', [MatriculaAdicionalController::class, 'getById'])->middleware('can:ver mis matriculas_adicionales');
        Route::patch('/matricula-adicional/{id}/rechazar', [MatriculaAdicionalController::class, 'rechazar'])->middleware('can:ver matriculas_especialidad'); 
        Route::patch('/matricula-adicional/aprobar-dc/{id}', [MatriculaAdicionalController::class, 'aprobarPorDC'])->middleware('can:ver matriculas_especialidad');
        Route::patch('/matricula-adicional/aprobar-sa/{id}', [MatriculaAdicionalController::class, 'aprobarPorSA'])->middleware('can:ver matriculas_especialidad');

        Route::get('/semestres', [SemestreController::class, 'indexAll'])->middleware('can:ver semestres');
        Route::get('/semestres/paginated', [SemestreController::class, 'index'])->middleware('can:ver semestres');
        Route::get('/semestres/last', [SemestreController::class, 'getLastSemestre'])->middleware('can:ver semestres');
        Route::post('/semestres', [SemestreController::class, 'store'])->middleware('can:crear semestres');
        Route::delete('/semestres/eliminarSemestres', [SemestreController::class, 'destroyMultiple']);
        Route::get('/semestres/{id}', [SemestreController::class, 'show'])->middleware('can:ver semestres');
        Route::put('/semestres/{id}', [SemestreController::class, 'update'])->middleware('can:editar semestres');
        Route::delete('/semestres/{id}', [SemestreController::class, 'destroy'])->middleware('can:eliminar semestres');


        Route::get('/usuarios', [UsuarioController::class, 'index'])->middleware('can:ver usuarios');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->middleware('can:crear usuarios');
        Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])->middleware('can:ver usuarios');
        Route::get('/usuarios/{id}/roles', [RolePermissionsController::class, 'listUserRoles'])->middleware('can:ver roles');
        Route::get('/usuarios/{id}/permissions', [RolePermissionsController::class, 'listUserPermissions'])->middleware('can:ver permisos');
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->middleware('can:editar usuarios');
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->middleware('can:eliminar usuarios');


        Route::get('/estudiantes', [EstudianteController::class, 'index'])->middleware('can:ver estudiantes');
        Route::post('/estudiantes', [EstudianteController::class, 'store'])->middleware('can:crear estudiantes');
        Route::post('/estudiantes/multiple', [EstudianteController::class, 'storeMultiple'])->middleware('can:crear estudiantes');
        Route::get('/estudiantes/{codEstudiante}', [EstudianteController::class, 'show'])->middleware('can:ver estudiantes');
        Route::put('/estudiantes/{codEstudiante}', [EstudianteController::class, 'update'])->middleware('can:editar estudiantes');
        Route::delete('/estudiantes/{codEstudiante}', [EstudianteController::class, 'destroy'])->middleware('can:eliminar estudiantes');


        Route::get('/docentes', [DocenteController::class, 'index'])->middleware('can:ver docentes');
        Route::post('/docentes', [DocenteController::class, 'store'])->middleware('can:crear docentes');
        Route::post('/docentes/multiple', [DocenteController::class, 'storeMultiple'])->middleware('can:crear docentes');
        Route::get('/docentes/{codDocente}', [DocenteController::class, 'show'])->middleware('can:ver docentes');
        Route::put('/docentes/{codDocente}', [DocenteController::class, 'update'])->middleware('can:editar docentes');
        Route::delete('/docentes/{codDocente}', [DocenteController::class, 'destroy'])->middleware('can:eliminar docentes');


        Route::get('/administrativos', [AdministrativoController::class, 'index'])->middleware('can:ver administrativos');
        Route::post('/administrativos', [AdministrativoController::class, 'store'])->middleware('can:crear administrativos');
        Route::post('/administrativos/multiple', [AdministrativoController::class, 'storeMultiple'])->middleware('can:crear administrativos');
        Route::get('/administrativos/{codAdministrativo}', [AdministrativoController::class, 'show'])->middleware('can:ver administrativos');
        Route::put('/administrativos/{codAdministrativo}', [AdministrativoController::class, 'update'])->middleware('can:editar administrativos');
        Route::delete('/administrativos/{codAdministrativo}', [AdministrativoController::class, 'destroy'])->middleware('can:eliminar administrativos');


        Route::post('/usuarios/sync-roles', [RolePermissionsController::class, 'syncRoles'])->middleware('can:asignar roles');
        Route::post('/usuarios/sync-permissions', [RolePermissionsController::class, 'syncPermissions'])->middleware('can:asignar permisos');


        Route::get('/roles', [RolePermissionsController::class, 'indexRoles'])->middleware('can:ver roles');
        Route::get('/roles/paginated', [RolePermissionsController::class, 'indexRolesPaginated'])->middleware('can:ver roles');
        Route::post('/roles', [RolePermissionsController::class, 'storeRole'])->middleware('can:crear roles');
        Route::get('/roles/{id}', [RolePermissionsController::class, 'showRole'])->middleware('can:ver roles');
        Route::put('/roles/{id}', [RolePermissionsController::class, 'updateRole'])->middleware('can:editar roles');
        Route::delete('/roles/{id}', [RolePermissionsController::class, 'destroyRole'])->middleware('can:eliminar roles');


        Route::get('/permissions', [RolePermissionsController::class, 'indexPermissions'])->middleware('can:ver permisos');
        Route::get('/permissions/paginated', [RolePermissionsController::class, 'indexPermissionsPaginated'])->middleware('can:ver permisos');
        Route::get('/permissions/{id}', [RolePermissionsController::class, 'showPermission'])->middleware('can:ver permisos');
        Route::put('/permissions/{id}', [RolePermissionsController::class, 'updatePermission'])->middleware('can:editar permisos');

        Route::get('/unidades/mine', [AuthController::class, 'getMyUnidades']);

        Route::post('/notificaciones/enviar', [NotificacionController::class, 'sendNotificationToEspecialidad']);  // Enviar notificación a todos los estudiantes de una especialidad
Route::get('/notificaciones/{estudianteId}/no-leidas', [NotificacionController::class, 'getUnreadNotificationsForStudent']);  // Obtener notificaciones no leídas para un estudiante
Route::patch('/notificaciones/{id}/marcar-como-leida', [NotificacionController::class, 'marcarComoLeida']);  // Marcar una notificación como leída


        Route::get('/estudiantesRiesgo/listar_profesor', [EstudianteRiesgoController::class, 'listar_por_especialidad_profesor']);
        Route::get('/estudiantesRiesgo/listar_director', [EstudianteRiesgoController::class, 'listar_por_especialidad_director']);
        Route::get('/estudiantesRiesgo/listar_informes', [EstudianteRiesgoController::class, 'listar_informes_estudiante']);
        Route::put('/estudiantesRiesgo/actualizar_informe', [EstudianteRiesgoController::class, 'actualizar_informe_estudiante']);
        Route::post('/estudiantesRiesgo/carga_alumnos', [EstudianteRiesgoController::class, 'carga_alumnos_riesgo']);
        Route::post('/estudiantesRiesgo/crear_informes', [EstudianteRiesgoController::class, 'crear_informes']);
        Route::get('/estudiantesRiesgo/obtener_estadisticas_informes', [EstudianteRiesgoController::class, 'obtener_estadisticas_informes']);
        Route::get('/estudiantesRiesgo/listar_informes_director', [EstudianteRiesgoController::class, 'listar_informes_director']);
        Route::get('/estudiantesRiesgo/listar_semanas_existentes/{id}', [EstudianteRiesgoController::class, 'listar_semanas_existentes']);
        Route::delete('/estudiantesRiesgo/eliminar_semana', [EstudianteRiesgoController::class, 'eliminar_semana']);
        Route::get('/temas-de-tesis', [TemaDeTesisController::class, 'indexPaginated'])->middleware('can:ver temas de tesis');
        Route::get('/temas-de-tesis/{id}', [TemaDeTesisController::class, 'show'])->middleware('can:ver temas de tesis');
        Route::put('/temas-de-tesis/{id}', [TemaDeTesisController::class, 'update'])->middleware('can:editar temas de tesis');
    });
});

/*Route::prefix('v1')->group(function () {
    Route::get('/estudiantesRiesgo/obtener_estadisticas_informes', [EstudianteRiesgoController::class, 'obtener_estadisticas_informes']);
});*/

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

//Ruta que con el ID del estudiante te saca todos los cursos en los que esta matriculado el semestre actual (JP)
Route::get('/estudiantes/{estudianteId}/cursos', [HorarioController::class, 'obtenerCursosEstudiante']);

//Ruta que con el ID de horario (obtenido de la previa ruta) te da un listado de los Jps para evaluar
Route::get('/horarios/{horarioId}/jps', [HorarioController::class, 'obtenerJps']);

//Ruta que con el ID del estudiante puede listar el curso y los docentes que está matriculado el semestre actual
Route::get('/estudiantes/{estudianteId}/encuestas-docentes', [HorarioController::class, 'obtenerEncuestasDocentesEstudiante']);

//Ruta que te muestra los datos de una encuesta, las preguntas asociadas, si fuera JP te pide el idJP, si fuera docente no necesita
Route::get('/encuestas/{encuestaId}/horarios/{horarioId}/{jpId?}', [EncuestaController::class, 'obtenerDetalleEncuesta']);

//Ruta para guardar los resultados de las preguntas de la encuesta para un determinado horario
//Me tiene que llegar como request el idEstudiante y las respuestas de lo que ha marcado, tambien jp_horario_id
//Actualmente solo sirve para guardar respuestas de docentes y JPs que no sean texto
Route::post('/encuestas/{encuestaId}/horarios/{horarioId}/respuestas', [EncuestaController::class, 'registrarRespuestas']);


//Usando encuestaID obtengo la lista de cursos asociados a esa encuesta
Route::get('/encuestas/{encuestaId}/cursos', [EncuestaController::class, 'obtenerCursosEncuesta']);

//Usando el cursoId previo, obtengo la lista de docentes
Route::get('/cursos/{cursoId}/docentes', [CursoController::class, 'obtenerDocentesPorCurso']);

//Usando el cursoId previo, obtengo la lista de horarios (JP)
Route::get('/cursos/{cursoId}/horarios', [CursoController::class, 'obtenerHorariosPorCurso']);


//Usando la encuestaId de arriba, junto con cursoId y horarioId devuelto obtengo los resultados para ese docente
Route::get('/resultados/docentes/encuestas/{encuestaId}/horarios/{horarioId}', [EncuestaController::class, 'obtenerResultadosDetalleDocente']);

Route::get('/resultados/jefes-practica/encuestas/{encuestaId}/jp-horarios/{jpHorarioId}', [EncuestaController::class, 'obtenerResultadosDetalleJp']);

//Notificaciones 





Route::get('/semestreActual', [SemestreController::class, 'obtenerSemestreActual']);

Route::prefix('v1')->group(function () {
    Route::post('/images/upload', [ImageController::class, 'upload']);
    Route::get('/images/{filename}', [ImageController::class, 'getMIME']);



    Route::get('pedidos-cursos', [PedidoCursosController::class, 'index']);
    Route::patch('/pedidos-cursos/enviar-multiples', [PedidoCursosController::class, 'enviarMultiplesPedidos']);
    Route::get('facultades/{facultad}/pedidos-cursos', [PedidoCursosController::class, 'getByFacultad']);

    Route::get('/especialidades/{especialidad}/cursos-pedido', [PedidoCursosController::class, 'getCursosPorEspecialidad']);
});
Route::get('/encuestas/{especialidad_id}/{tipo_encuesta}', [EncuestaController::class, 'indexEncuesta']);
Route::get('/encuestas-nueva-cursos/{especialidad_id}', [EncuestaController::class, 'indexCursoSemestreEspecialidad']);
Route::get('/encuestas-nueva-cant/{especialidad_id}/{tipo_encuesta}', [EncuestaController::class, 'countPreguntasLatestEncuesta']);
Route::get('/encuestas-nueva-preg/{especialidad_id}/{tipo_encuesta}', [EncuestaController::class, 'obtenerPreguntasUltimaEncuesta']);
Route::post('/encuestas-nueva/{especialidad_id}/{tipo_encuesta}', [EncuestaController::class, 'registrarNuevaEncuesta']);
Route::get('/encuestas-cursos/{encuesta_id}', [EncuestaController::class, 'mostrarCursos']);
Route::get('/encuestas-preguntas/{encuesta_id}', [EncuestaController::class, 'listarPreguntas']);
Route::put('/encuestas/{especialidad_id}/{encuesta_id}', [EncuestaController::class, 'gestionarEncuesta']);
