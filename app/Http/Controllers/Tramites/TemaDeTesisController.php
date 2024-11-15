<?php

namespace App\Http\Controllers\Tramites;

use App\Http\Controllers\Controller;
use App\Models\Authorization\PermissionCategory;
use App\Models\Tramites\EstadoAprobacionTema;
use App\Models\Tramites\ProcesoAprobacionTema;
use App\Models\Tramites\TemaDeTesis;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class TemaDeTesisController extends Controller
{
    // Método para listar temas de tesis con filtros y paginación
    public function indexPaginated(Request $request)
    {
        $search = $request->input('search', '');
        $per_page = $request->input('per_page', 10);
        $facultad_id = $request->input('facultad_id', null);
        $especialidad_id = $request->input('especialidad_id', null);
        $estado_jurado = $request->input('estado_jurado', null);
        $rol = $request->input('rol', null); // Nuevo parámetro para el rol

        $query = TemaDeTesis::with([
            'especialidad',
            'jurados.usuario',
            'asesores.usuario',
            'estudiantes.usuario'
        ])
            ->when($facultad_id, function ($query) use ($facultad_id) {
                $query->whereHas('especialidad', function ($q) use ($facultad_id) {
                    $q->where('facultad_id', $facultad_id);
                });
            })
            ->when($especialidad_id, function ($query) use ($especialidad_id) {
                $query->where('especialidad_id', $especialidad_id);
            })
            ->when($estado_jurado, function ($query) use ($estado_jurado) {
                $query->where('estado_jurado', $estado_jurado);
            })
            ->when($rol === 'director', function ($query) {
                $query->whereIn('estado_jurado', ['vencido', 'desaprobado', 'aprobado', 'enviado', 'pendiente']);
            })
            ->where('estado', 'aprobado');

        // Filtrar por términos de búsqueda
        if ($search) {
            $terms = explode(' ', $search);
            foreach ($terms as $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('titulo', 'like', "%$term%")
                        ->orWhere('resumen', 'like', "%$term%")
                        ->orWhere('estado_jurado', 'like', "%$term%")
                        ->orWhereHas('estudiantes', function ($q) use ($term) {
                            $q->where('codigoEstudiante', 'like', "%$term%")
                                ->orWhereHas('usuario', function ($q) use ($term) {
                                    $q->where('nombre', 'like', "%$term%")
                                        ->orWhere('apellido_paterno', 'like', "%$term%")
                                        ->orWhere('apellido_materno', 'like', "%$term%");
                                });
                        });
                });
            }
        }

        $temasDeTesis = $query->paginate($per_page);

        return response()->json($temasDeTesis, 200);
    }


    // Método para mostrar un tema de tesis específico
    public function show($id)
    {
        $temaDeTesis = TemaDeTesis::with([
            'especialidad',
            'jurados.usuario',      // Cargar los datos del usuario de cada jurado
            'asesores.usuario',     // Cargar los datos del usuario de cada asesor
            'estudiantes.usuario',  // Cargar los datos del usuario de cada estudiante
            // 'observaciones'
        ])
            ->findOrFail($id);

        return response()->json($temaDeTesis, 200);
    }

    // Método para actualizar el estado y estado del jurado de un tema de tesis
    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'nullable|in:aprobado,pendiente,desaprobado',
            'estado_jurado' => 'nullable|in:enviado,no enviado,aprobado,pendiente,desaprobado,vencido',
            'jurados' => 'nullable|array',
            'jurados.*' => 'exists:docentes,id',
            'comentarios' => 'nullable|string', // Validación de comentarios
        ]);

        $temaDeTesis = TemaDeTesis::findOrFail($id);

        // Actualización de estado, estado_jurado y comentarios
        $temaDeTesis->update($request->only('estado', 'estado_jurado', 'comentarios'));

        // Actualización de jurados, si se proveen
        if ($request->has('jurados')) {
            $temaDeTesis->jurados()->sync($request->jurados);
        }

        return response()->json(['message' => 'Tema de Tesis actualizado exitosamente', 'tema' => $temaDeTesis], 200);
    }

    public function indexTemasEstudianteId($estudiante_id): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($estudiante_id);
        $temasDeTesis = $estudiante->temasDeTesis->map(function ($tema) {
            return [
                'id' => $tema->id,
                'titulo' => $tema->titulo,
                'estado' => $tema->estado,
            ];
        });
        return response()->json($temasDeTesis, 200);
    }

    public function indexTemasPendientesUsuarioId($usuario_id): JsonResponse
    {
        $temasPendientes = TemaDeTesis::whereHas('procesoAprobacion', function ($query) use ($usuario_id) {
            $query->whereHas('estadoAprobacion', function ($query) use ($usuario_id) {
                $query->where('usuario_id', $usuario_id)
                    ->where('estado', 'pendiente');
            });
        })->get();

        return response()->json([
            'temasPendientes' => $temasPendientes
        ], 200);
    }

    public function listarAreasEspecialidad($estudiante_id): JsonResponse
    {
        $estudiante = Estudiante::with('especialidad.areas')->find($estudiante_id);
        if (!$estudiante) {
            return response()->json([
                'message' => 'Estudiante no encontrado.'
            ], 404);
        }

        $areas = $estudiante->especialidad->areas;
        return response()->json([
            'areas' => $areas
        ], 200);
    }

    public function listarDocentesEspecialidad($estudiante_id): JsonResponse
    {
        $estudiante = Estudiante::with('especialidad.docentes')->find($estudiante_id);
        $docentes = $estudiante->especialidad->docentes;
        $docentesConNombre = $docentes->map(function ($docente) {
            return [
                'id' => $docente->id,
                'usuario_id' => $docente->usuario_id,
                'codigoDocente' => $docente->codigoDocente,
                'nombre_completo' => $docente->usuario->full_name,
                'tipo' => $docente->tipo,
                'especialidad_id' => $docente->especialidad_id,
            ];
        });
        return response()->json([
            'docentes' => $docentesConNombre
        ], 200);
    }


    public function registrarTema(Request $request): JsonResponse
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'titulo' => 'required|string|max:255',
            'resumen' => 'required|string',
            'area_id' => 'required|exists:areas,id',
            'docente_id' => 'required|exists:docentes,id',
        ]);

        DB::beginTransaction();
        try {
            // Buscar al estudiante y obtener la especialidad
            $estudiante = Estudiante::findOrFail($request->estudiante_id);
            $especialidad_id = $estudiante->especialidad_id;

            // Registrar el tema de tesis
            $temaTesis = TemaDeTesis::create([
                'titulo' => $request->titulo,
                'resumen' => $request->resumen,
                'especialidad_id' => $especialidad_id,
                'area_id' => $request->area_id,
                'estado' => 'pendiente',
                'fecha_enviado' => Now()
            ]);

            // Registrar los asesores
            $temaTesis->asesores()->attach($request->docente_id);

            // Registrar la entrada en proceso_aprobacion_tema
            $procesoAprobacion = ProcesoAprobacionTema::create([
                'tema_tesis_id' => $temaTesis->id,
                'fecha_inicio' => Carbon::now(),
                'estado_proceso' => 'pendiente',
            ]);

            // Registrar el estado de aprobación para el asesor
            $docente = Docente::find($request->docente_id);
            $usuarioId = $docente->usuario_id; // Obtener el usuario_id del docente

            EstadoAprobacionTema::create([
                'proceso_aprobacion_id' => $procesoAprobacion->id,
                'usuario_id' => $usuarioId,
                'estado' => 'pendiente',
                'responsable' => 'asesor'
            ]);

            $estudiante->temasDeTesis()->attach($temaTesis->id);

            DB::commit();

            return response()->json([
                'message' => 'Tema de tesis registrado correctamente.',
                'tema_tesis' => $temaTesis,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Hubo un error al registrar el tema de tesis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function aprobarTemaUsuario(Request $request, $tema_tesis_id): JsonResponse
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'comentarios' => 'nullable|string',
            //aqui falta el archivo
        ]);

        $temaTesis = TemaDeTesis::with('procesoAprobacion.estadoAprobacion')->find($tema_tesis_id);

        $procesoAprobacion = $temaTesis->procesoAprobacion;
        $estadoAprobacion = $procesoAprobacion ? $procesoAprobacion->estadoAprobacion()->orderBy('id', 'desc')->first() : null;
        $responsable = $estadoAprobacion->responsable;

        if ($responsable === 'director') {
            DB::beginTransaction();
            try {
                if ($estadoAprobacion) {
                    $estadoAprobacion->update([
                        'fecha_decision' => now(),
                        'estado' => 'aprobado',
                        'comentarios' => $request->comentarios,
                    ]);
                }
                if ($procesoAprobacion) {
                    $procesoAprobacion->update([
                        'fecha_fin' => now(),
                        'estado_proceso' => 'aprobado',
                    ]);
                }
                $temaTesis->update([
                    'estado' => 'aprobado',
                ]);
                DB::commit();
                return response()->json([
                    'message' => 'Tema de tesis aprobado correctamente.',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Hubo un error al aprobar el tema de tesis.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        } elseif ($responsable === 'coordinador') {
            DB::beginTransaction();
            try {
                if ($estadoAprobacion) {
                    $estadoAprobacion->update([
                        'fecha_decision' => now(),
                        'estado' => 'aprobado',
                        'comentarios' => $request->comentarios,
                    ]);
                    $director = Usuario::find(109); // Falta tener una forma de saber quien es el director de carrera
                    $usuarioId = $director->id;
                    EstadoAprobacionTema::create([
                        'proceso_aprobacion_id' => $procesoAprobacion->id,
                        'usuario_id' => $usuarioId,
                        'estado' => 'pendiente',
                        'responsable' => 'director'
                    ]);
                }
                DB::commit();
                return response()->json([
                    'message' => 'Proceso de aprobación actualizado correctamente.',
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Error al actualizar el proceso de aprobación.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        } else {
            DB::beginTransaction();
            try {
                $estadoAprobacion->update([
                    'fecha_decision' => now(),
                    'estado' => 'aprobado',
                    'comentarios' => $request->comentarios,
                ]);
                $coordinador = Usuario::find(108); // Falta tener una forma de saber quien es el coordinador de un area
                $usuarioId = $coordinador->id;
                EstadoAprobacionTema::create([
                    'proceso_aprobacion_id' => $procesoAprobacion->id,
                    'usuario_id' => $usuarioId,
                    'estado' => 'pendiente',
                    'responsable' => 'coordinador',
                ]);
                DB::commit();
                return response()->json([
                    'message' => 'Tema de tesis aprobado correctamente.',
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Error al actualizar el proceso de aprobación.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }

    public function rechazarTemaUsuario(Request $request, $tema_tesis_id): JsonResponse
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'comentarios' => 'nullable|string',
            // Aquí falta la validación del archivo si es necesario
        ]);
        DB::transaction(function () use ($tema_tesis_id, $request) {
            // Obtener el tema de tesis con sus relaciones
            $temaTesis = TemaDeTesis::with('procesoAprobacion.estadoAprobacion')->findOrFail($tema_tesis_id);

            $procesoAprobacion = $temaTesis->procesoAprobacion;
            $estadoAprobacion = $procesoAprobacion ? $procesoAprobacion->estadoAprobacion()->orderBy('id', 'desc')->first() : null;

            if ($estadoAprobacion) {
                $estadoAprobacion->update([
                    'fecha_decision' => now(),
                    'estado' => 'rechazado',
                    'comentarios' => $request->comentarios,
                ]);
            }

            if ($procesoAprobacion) {
                $procesoAprobacion->update([
                    'fecha_fin' => now(),
                    'estado_proceso' => 'rechazado',
                ]);
            }

            $temaTesis->update([
                'estado' => 'desaprobado',
            ]);
        });

        return response()->json([
            'message' => 'Tema de tesis rechazado correctamente.',
        ]);
    }
}
