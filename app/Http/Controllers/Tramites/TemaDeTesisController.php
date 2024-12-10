<?php

namespace App\Http\Controllers\Tramites;

use App\Http\Controllers\Controller;
use App\Models\Authorization\PermissionCategory;
use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Storage\File;

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
        })
        ->with(['estudiantes:usuario_id'])
        ->select('id', 'titulo', 'estado')
        ->get();

        $temasPendientes = $temasPendientes->map(function ($tema) {
            $tema->estudiante = $tema->estudiantes->first()->usuario->full_name;
            unset($tema->estudiantes);
            return $tema;
        });

        return response()->json($temasPendientes, 200);
    }


    public function listarAreasEspecialidad($estudiante_id): JsonResponse
    {
        $estudiante = Estudiante::with('especialidad.areas')->find($estudiante_id);
        if (!$estudiante) {
            return response()->json([
                'message' => 'Estudiante no encontrado.'
            ], 404);
        }

        $areas = $estudiante->especialidad->areas->map(function ($area) {
            return [
                'id' => $area->id,
                'nombre' => $area->nombre,
            ];
        });

        return response()->json([
            'areas' => $areas
        ], 200);
    }

    public function listarDocentesEspecialidad($estudiante_id): JsonResponse
    {
        $estudiante = Estudiante::with('especialidad.docentes')->find($estudiante_id);
        if (!$estudiante) {
            return response()->json([
                'message' => 'Estudiante no encontrado.'
            ], 404);
        }
        $docentes = $estudiante->especialidad->docentes;
        $docentesConNombre = $docentes->map(function ($docente) {
            return [
                'id' => $docente->id,
                'nombre' => $docente->usuario->full_name,
            ];
        });
        return response()->json($docentesConNombre, 200);
    }

    public function subirArchivo(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'file_type' => ['required', Rule::in(['document'])],
                'file' => 'required|file|mimes:pdf,doc,docx|max:2048',
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $path = 'files/tema-tesis/';

            // Crear un nombre único para el archivo si ya existe
            $uniqueName = $originalName;
            $counter = 1;
            while (Storage::disk('s3')->exists($path . $uniqueName)) {
                // Si el archivo ya existe, agregar un número al final del nombre
                $uniqueName = pathinfo($originalName, PATHINFO_FILENAME) . '-' . $counter . '.' . $file->getClientOriginalExtension();
                $counter++;
            }

            // Subir el archivo a S3 con el nombre único
            Storage::disk('s3')->put($path . $uniqueName, file_get_contents($file));

            // Crear el registro en la base de datos para almacenar la información del archivo
            $fileRecord = File::create([
                'name' => $uniqueName,
                'file_type' => $request->file_type,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'path' => $path . $uniqueName,
                'url' => Storage::url($path . $uniqueName),
            ]);

            // Devolver la URL y el objeto del archivo creado
            return response()->json(['url' => $fileRecord->url, 'file' => $fileRecord], 201);

        } catch (\Exception $e) {
            Log::error('File Upload Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error uploading tema de tesis file: ' . $e->getMessage()], 500);
        }
    }



    public function registrarTema(Request $request): JsonResponse
    {
        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'titulo' => 'required|string|max:255',
            'resumen' => 'required|string',
            'area_id' => 'required|exists:areas,id',
            'docente_id' => 'required|exists:docentes,id',
            'documento' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mkv,mp3,wav,pdf,doc,docx,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('documento');
            $titulo_modificado = strtolower(str_replace(' ', '-', $request->titulo));
            if (!$file || !$file->isValid()) {
                return response()->json(['message' => 'El archivo no es válido o no se ha recibido.'], 400);
            }
            $uploadRequest = new Request([
                'name' => $titulo_modificado,
                'file_type' => 'document',
                'file' => $file
            ]);

            
            $uploadRequest->files->set('file', $file);
            $fileResponse = $this->subirArchivo($uploadRequest);
            $fileId = $fileResponse->getData()->file->id;

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
                'fecha_enviado' => Now(),
                'file_id' => $fileId,
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

    public function actualizarTema(Request $request, $idTesis): JsonResponse
    {
        $request->validate([
            'titulo' => 'nullable|string|max:255',
            'resumen' => 'nullable|string',
            'area_id' => 'nullable|exists:areas,id',
            'docente_id' => 'nullable|exists:docentes,id',
            'documento' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        Log::info('Datos recibidos en la solicitud:', $request->all());

        DB::beginTransaction();
        try {
            // Buscar el tema existente
            $temaTesis = TemaDeTesis::findOrFail($idTesis);

            // Actualizar archivo si se proporciona uno nuevo
            if ($request->hasFile('documento')) {
                $file = $request->file('documento');
                if (!$file || !$file->isValid()) {
                    return response()->json(['message' => 'El archivo no es válido o no se ha recibido.'], 400);
                }

                $titulo_modificado = strtolower(str_replace(' ', '-', $request->titulo));
                $uploadRequest = new Request([
                    'name' => $titulo_modificado,
                    'file_type' => 'document',
                    'file' => $file,
                ]);

                $uploadRequest->files->set('file', $file);
                $fileResponse = $this->subirArchivo($uploadRequest);
                $fileData = $fileResponse->getData();

                if (!isset($fileData->file) || !isset($fileData->file->id)) {
                    return response()->json(['message' => 'Error al subir el archivo.'], 400);
                }

                $fileId = $fileData->file->id;

                // Actualizar el campo file_id
                $temaTesis->update(['file_id' => $fileId]);
            }
            
            // Actualizar los datos básicos del tema
            $temaTesis->update(array_filter([
                'titulo' => $request->titulo ?? $temaTesis->titulo, // Solo actualizar si se envía un nuevo título
                'resumen' => $request->resumen ?? $temaTesis->resumen, // Solo actualizar si se envía un nuevo resumen
                'area_id' => $request->area_id ?? $temaTesis->area_id, // Solo actualizar si se envía una nueva área
                'estado' => 'pendiente',
                'file_firmado_id' => null,
            ]));

            // Actualizar los asesores del tema
            if ($request->docente_id) {
                $temaTesis->asesores()->sync([$request->docente_id]);
            }

            // Modificar el estado en la tabla estado_aprobacion_tema cuando el responsable sea "asesor"
            // Cambiar el estado a "pendiente"
            DB::table('estado_aprobacion_tema')
                ->where('proceso_aprobacion_id', $idTesis)
                ->where('responsable', 'asesor')
                ->update(['estado' => 'pendiente', 'comentarios' => null]);

            // Eliminar las filas donde el responsable sea "coordinador" o "director"
            DB::table('estado_aprobacion_tema')
                ->where('proceso_aprobacion_id', $idTesis)
                ->whereIn('responsable', ['coordinador', 'director'])
                ->delete();

            DB::commit();

            return response()->json([
                'message' => 'Tema de tesis actualizado correctamente.',
                'tema_tesis' => $temaTesis,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Hubo un error al actualizar el tema de tesis.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    private function obtenerUsuario($rol, $entidad_id){
        $rol_id = Role::findByName($rol)->id;
        return RoleScopeUsuario::where('role_id', $rol_id)
            ->where('entity_id', $entidad_id)
            ->orderBy('id', 'desc') // Asegúrate de ordenar por el campo adecuado
            ->first()
            ->usuario_id ?? null;
    }

    public function aprobarTemaUsuario(Request $request, $tema_tesis_id): JsonResponse
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'documento_firmado' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ]);

        $temaTesis = TemaDeTesis::with('procesoAprobacion.estadoAprobacion')->find($tema_tesis_id);

        $procesoAprobacion = $temaTesis->procesoAprobacion;
        $estadoAprobacion = $procesoAprobacion ? $procesoAprobacion->estadoAprobacion()->orderBy('id', 'desc')->first() : null;
        $responsable = $estadoAprobacion->responsable;

        if ($request->hasFile('documento_firmado')) {
            $file = $request->file('documento_firmado');
            if ($file->isValid()) {
                $titulo_modificado = strtolower(str_replace(' ', '-', $temaTesis->titulo));
                $numero = $responsable === "director" ? 1 : ($responsable === "coordinador" ? 2 : 3);
                $uploadRequest = new Request([
                    'name' => $titulo_modificado . '-' . $numero,
                    'file_type' => 'document',
                    'file' => $file
                ]);
                $uploadRequest->files->set('file', $file);
        
                $fileResponse = $this->subirArchivo($uploadRequest);
                $fileData = $fileResponse->getData();
        
                if (!isset($fileData->file) || !isset($fileData->file->id)) {
                    return response()->json(['message' => 'Error al subir el archivo firmado.'], 400);
                }
        
                $fileId = $fileData->file->id;
                $temaTesis->update(['file_firmado_id' => $fileId]);
            } else {
                return response()->json(['message' => 'El archivo firmado no es válido.'], 400);
            }
        }        

        if ($responsable === 'director') {
            DB::beginTransaction();
            try {
                if ($estadoAprobacion) {
                    $estadoAprobacion->update([
                        'fecha_decision' => now(),
                        'estado' => 'aprobado',
                        'file_id' => $fileId,
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
                        'file_id' => $fileId,
                    ]);

                    $usuarioId = $this->obtenerUsuario('director', $temaTesis->especialidad_id);
                    if ($usuarioId) {
                        EstadoAprobacionTema::create([
                            'proceso_aprobacion_id' => $procesoAprobacion->id,
                            'usuario_id' => $usuarioId,
                            'estado' => 'pendiente',
                            'responsable' => 'director'
                        ]);
                    } else {
                        return response()->json([
                            'message' => 'Esta especialidad no tiene un director'
                        ]);
                    }
                    DB::commit();
                    return response()->json([
                        'message' => 'Proceso de aprobación actualizado correctamente.',
                    ], 200);
                }
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
                    'file_id' => $fileId,
                ]);
                $usuarioId = $this->obtenerUsuario('coordinador', $temaTesis->area_id);
                if($usuarioId){
                    EstadoAprobacionTema::create([
                        'proceso_aprobacion_id' => $procesoAprobacion->id,
                        'usuario_id' => $usuarioId,
                        'estado' => 'pendiente',
                        'responsable' => 'coordinador',
                    ]);
                }
                else{
                    return response()->json([
                        'message'=> 'Esta area no tiene un coordinador'
                    ]);
                }
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

    public function verDetalleTema($tema_tesis_id): JsonResponse{
        $tema = TemaDeTesis::with([
            'procesoAprobacion.estadoAprobacion.usuario',
        ])->find($tema_tesis_id);

        if (!$tema) {
            return response()->json(['error' => 'Tema de Tesis no encontrado'], 404);
        }

        // Obtener el proceso de aprobación relacionado
        $procesoAprobacion = $tema->procesoAprobacion;

        // Obtener estados de aprobación ordenados cronológicamente
        $estadosAprobacion = $procesoAprobacion
            ? $procesoAprobacion->estadoAprobacion()->with('usuario')->orderBy('id', 'asc')->get()
            : [];

        // Determinar el estado general
        $estadoGeneral = 'Pendiente';
        foreach ($estadosAprobacion as $estado) {
            if ($estado->estado === 'rechazado') {
                $estadoGeneral = 'Rechazado';
                break;
            }
            if ($estado->estado === 'aprobado') {
                $estadoGeneral = 'Aprobado';
            }
        }

        // Configuración de roles evaluadores en orden
        $rolesEvaluadores = [
            'asesor' => 'Asesor',
            'coordinador' => 'Coordinador de Área',
            'director' => 'Director de Carrera',
        ];

        // Construir revisiones, asegurando que todos los roles se incluyan
        $revisiones = [];
        foreach ($rolesEvaluadores as $responsable => $rol) {
            $estado = $estadosAprobacion->firstWhere('responsable', $responsable);

            $revisiones[] = [
                'rol' => $rol,
                'estado' => $estado ? ucfirst($estado->estado) : 'Esperando',
                'fecha' => $estado && $estado->fecha_decision ? $estado->fecha_decision : null,
                'revisor' => $estado && $estado->usuario ? $estado->usuario->nombre : null,
            ];
        }

        // Construir detalle de revisión, excluyendo pendientes
        $detalleRevision = [];
        foreach ($estadosAprobacion as $estado) {
            if ($estado->estado === 'aprobado' || $estado->estado === 'rechazado') {
                $estadoDetalle = $estado->estado === 'aprobado' ? 'aprobado' : 'rechazado';
                $mensaje = $estado->estado === 'aprobado'
                    ? 'Tu tema ha sido aprobado'
                    : 'Tu tema ha sido rechazado';

                $detalleRevision[] = [
                    'mensaje' => $mensaje,
                    'fecha' => $estado->fecha_decision ? $estado->fecha_decision : null,
                    'estado' => $estadoDetalle,
                    'revisor' => $estado->usuario ? $estado->usuario->nombre : null,
                    'comentarios' => $estado->comentarios ?? null,
                ];
            }
        }

        // Agregar detalle de envío inicial
        $envio = [
            'mensaje' => 'Tu tema ha sido enviado correctamente',
            'fecha' => $procesoAprobacion ? $procesoAprobacion->fecha_inicio : null,
            'estado' => 'informativo',
        ];

        // Fecha de última actualización
        $ultimaActualizacion = $estadosAprobacion->last()
            ? $estadosAprobacion->last()->updated_at
            : null;

        // Construir JSON final
        $response = [
            'tema' => $tema->titulo,
            'estado_general' => $estadoGeneral,
            'envio' => $envio,
            'revisiones' => $revisiones,
            'detalle_revision' => $detalleRevision,
            'ultima_actualizacion' => $ultimaActualizacion,
        ];

        return response()->json($response);


    }

    public function descargarArchivo($tema_tesis_id)
    {
        try {
            $temaTesis = TemaDeTesis::findOrFail($tema_tesis_id);

            // Verificar si el tema de tesis tiene un archivo asociado
            if (!$temaTesis->archivo_path || !Storage::disk('s3')->exists($temaTesis->archivo_path)) {
                return response()->json(['message' => 'El archivo asociado al tema de tesis no se encuentra.'], 404);
            }

            // Obtener el archivo desde S3
            $contenidoArchivo = Storage::disk('s3')->get($temaTesis->archivo_path);

            // Obtener el nombre del archivo
            $nombreArchivo = basename($temaTesis->archivo_path); // Extrae solo el nombre del archivo de la ruta completa

            // Retornar el archivo para la descarga
            return response($contenidoArchivo, 200)
                ->header('Content-Type', Storage::disk('s3')->mimeType($temaTesis->archivo_path))
                ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');
        } catch (\Exception $e) {
            Log::error('Error al descargar archivo: ' . $e->getMessage());
            return response()->json(['message' => 'Error al intentar descargar el archivo.', 'error' => $e->getMessage()], 500);
        }
    }
}
