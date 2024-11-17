<?php

namespace App\Http\Controllers\Matricula;
use App\Models\Usuarios\Estudiante;
use App\Models\Matricula\CartaPresentacionSolicitud;
use App\Models\Matricula\HorarioEstudiante;
use App\Models\Storage\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CartaPresentacionController extends Controller
{
    // Mostrar todas las solicitudes de carta de presentación
    public function index()
    {
        $solicitudes = CartaPresentacionSolicitud::with(['estudiante', 'horario'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('solicitudes.lista_solicitudes', compact('solicitudes'));
    }

    // Mostrar el formulario para hacer una solicitud de carta de presentación
    public function create()
    {
        return view('solicitudes.solicitar_carta');
    }

    // Almacenar una nueva solicitud
   

    // Generar el PDF para descarga (secretaria)
    public function generarPdf($id)
    {
        $solicitud = CartaPresentacionSolicitud::findOrFail($id);

        // Generar el PDF (solo la secretaria puede hacerlo)
        if ($solicitud->estado != 'Pendiente') {
            return back()->withErrors(['error' => 'La solicitud ya fue procesada']);
        }

        $pdfPath = $solicitud->generarPdf(); // Generar el PDF

        return response()->download(storage_path('app/public/carta_presentacion/' . basename($pdfPath)));
    }

    // Subir el PDF firmado (Director de carrera)
    public function subirPdfFirmado(Request $request, $id)
    {
        $solicitud = CartaPresentacionSolicitud::findOrFail($id);

        // Validar el archivo PDF
        $request->validate([
            'pdf_firmado' => 'required|file|mimes:pdf|max:10240', // Limitar el tamaño si es necesario
        ]);

        // Subir el PDF firmado
        $solicitud->subirPdfFirmado($request->file('pdf_firmado'));

        return redirect()->route('solicitudes.index')->with('success', 'PDF firmado y subido con éxito');
    }

    public function getByEstudiante(Request $request, $estudianteId)
    {
        // Recoger los filtros de búsqueda y estado
        $search = $request->input('search', ''); // Campo de búsqueda
        $estado = $request->input('estado', null); // Estado para filtrar
        $perPage = $request->input('per_Page', 10); // Cantidad de elementos por página

        // Comenzar la consulta
        $query = CartaPresentacionSolicitud::with([
            'estudiante.usuario',
            'horario',
            'horario.docentes.usuario:id,nombre,apellido_paterno',
            'horario.curso',
        ])
        ->where('estudiante_id', $estudianteId);

        $solicitudes = $query->get();

        // Verificar si no hay resultados
        if ($solicitudes->isEmpty()) {
            return response()->json(['message' => 'No se encontraron solicitudes para este estudiante.'], 404);
        }
        
        // Aplicar el filtro de búsqueda si el campo no está vacío
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('horario', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                })
                ->orWhereHas('horario.curso', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                })
                ->orWhereHas('horario.docentes.usuario', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $search . '%');
                });
            });
        }

        // Aplicar el filtro de estado si se ha seleccionado
        if ($estado) {
            $query->whereIn('estado', (array)$estado);
        }

        // Paginación
        $solicitudes = $query->paginate($perPage);

        $result = $solicitudes->map(function ($solicitud) {
            return [
                'id' => $solicitud->id, // Primer campo es id
                'profesor' => isset($solicitud->horario->docentes->first()->usuario)
                    ? $solicitud->horario->docentes->first()->usuario->nombre . ' ' . $solicitud->horario->docentes->first()->usuario->apellido_paterno
                    : 'Sin Profesor', // Profesor
                'curso' => $solicitud->horario->curso->nombre, // Curso
                'horario' => $solicitud->horario->codigo, // Horario
                'ultimaModificacion' => Carbon::parse($solicitud->updated_at)->format('d-m-Y'), // Última modificación
                'estado' => $solicitud->estado, // Estado
            ];
        });

        return response()->json([
            'data' => $result,
            'pagination' => [
                'total' => $solicitudes->total(), // Total de filas
                'current_page' => $solicitudes->currentPage(),
                'last_page' => $solicitudes->lastPage(),
                'per_page' => $solicitudes->perPage(),
            ],
        ]);
    }

    public function getSolicitudDetalle($id)
    {
        // Buscar la solicitud por su ID, incluyendo las relaciones necesarias
        $solicitud = CartaPresentacionSolicitud::with([
            'estudiante.usuario',     // Para obtener el nombre y correo del estudiante
            'estudiante',             // Obtener el estudiante completo
            'horario',                // Obtener el horario
            'horario.curso',          // Obtener el curso
            'especialidad',           // Obtener la especialidad
            'file'                    // Incluir el archivo relacionado
        ])->find($id);           // Si no encuentra la solicitud, arroja un error 404

        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud no encontrada.'], 404);
        }

        // Preparar la respuesta con los datos relacionados
        $resultado = [
            'id' => $solicitud->id,
            'estado' => $solicitud->estado,
            'motivo' => $solicitud->motivo,
            'motivo_rechazo' => $solicitud->estado === 'Rechazado' ? $solicitud->motivo_rechazo : null,
            'archivo' => $solicitud->file ? [
                'id' => $solicitud->file->id,
                'name' => $solicitud->file->name,
                'url' => $solicitud->file->url,
                'mime_type' => $solicitud->file->mime_type,
                'size' => $solicitud->file->size,
            ] : null, // Información del archivo o null si no hay archivo asociado
            'id_especialidad' => $solicitud->especialidad_id,
            'especialidad_nombre' => $solicitud->especialidad->nombre,
            'ultima_modificacion' => Carbon::parse($solicitud->updated_at)->format('d-m-Y'),
            'estudiante' => [
                'nombre_completo' => $solicitud->estudiante->usuario->nombre . ' ' . 
                                    $solicitud->estudiante->usuario->apellido_paterno . ' ' .
                                    $solicitud->estudiante->usuario->apellido_materno,
                'codigo_estudiante' => $solicitud->estudiante->codigoEstudiante,
                'correo' => $solicitud->estudiante->usuario->email,
            ],
            'curso' => [
                'nombre' => $solicitud->horario->curso->nombre,
                'codigo' => $solicitud->horario->codigo,
            ],
            'horario' => [
                'nombre' => $solicitud->horario->nombre,
                'codigo' => $solicitud->horario->codigo,
                'id' => $solicitud->horario->id,
            ]
        ];
        // Retornar la respuesta en formato JSON
        return response()->json($resultado);
    }


public function getCursosPorEstudiante($estudianteId)
{
    // Recupera los horarios del estudiante con el curso asociado
    $estudiante = Estudiante::with(['horarios' => function($query) {
        $query->with('curso');  // Cargar también el curso asociado a cada horario
    }])->find($estudianteId);

    return response()->json($estudiante->horarios);
}

    public function store(Request $request)
    {
        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'estudiante_id' => 'required|exists:estudiantes,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'motivo' => 'required|string|max:500',
            'estado' => 'nullable|in:Pendiente Secretaria,Pendiente Firma DC,Aprobado,Rechazado', // Se hace nullable
            'curso_id' => 'required|exists:cursos,id',
            'horario_id' => 'required|exists:horarios,id',
        ]);

        // Si la validación falla
        if ($validator->fails()) {
            // Obtiene los mensajes de error y los convierte en un formato amigable
            $messages = $validator->errors()->all();

            return response()->json([
                'error' => true,
                'message' => 'Parece que hay algunos campos faltantes o incorrectos: ' . implode(", ", $messages),
                'details' => $messages,
            ], 422); // 422 es un código HTTP para "unprocessable entity" (entidad no procesable)
        }

        try {
            // Establecer el estado como 'Pendiente Secretaria' si no se envía
            $estado = $request->estado ?? 'Pendiente Secretaria'; // Si el estado no se proporciona, se asigna el valor por defecto

            // Crear la solicitud de carta de presentación
            $solicitud = CartaPresentacionSolicitud::create([
                'estudiante_id' => $request->estudiante_id,
                'especialidad_id' => $request->especialidad_id,  // Asumimos que ahora se añade este campo
                'motivo' => $request->motivo,
                'estado' => $estado,
                'motivo_rechazo' => $request->motivo_rechazo,  // Puede ser null si no es rechazado
                'curso_id' => $request->curso_id,
                'horario_id' => $request->horario_id,
            ]);

            // Si la solicitud se crea correctamente
            return response()->json([
                'success' => true,
                'message' => '¡Tu solicitud ha sido guardada correctamente! Ahora puedes proceder con el siguiente paso.',
                'solicitud' => $solicitud,
            ], 201); // 201 es el código HTTP para "created"
        } catch (\Exception $e) {
            // Si algo sale mal, se captura la excepción
            return response()->json([
                'error' => true,
                'message' => 'Hubo un problema al procesar tu solicitud. Por favor, intenta de nuevo más tarde.',
                'details' => $e->getMessage(),
            ], 500); // 500 es un código HTTP para "internal server error"
        }
    }

public function getByEspecialidad(Request $request, $especialidadId)
{
    // Recoger los filtros de búsqueda y estado
    $search = $request->input('search', ''); // Campo de búsqueda
    $estado = $request->input('estado', null); // Estado para filtrar
    $perPage = $request->input('per_Page', 10); // Cantidad de elementos por página

    // Comenzar la consulta
    $query = CartaPresentacionSolicitud::with([
        'estudiante.usuario',
        'horario',
        'horario.docentes.usuario:id,nombre,apellido_paterno',
        'horario.curso',
    ])
    ->whereHas('estudiante', function ($query) use ($especialidadId) {
        $query->where('especialidad_id', $especialidadId);  // Filtramos los estudiantes por especialidad
    });

    // Aplicar el filtro de búsqueda si el campo no está vacío
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('horario', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%');
            })
            ->orWhereHas('horario.curso', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%');
            })
            ->orWhereHas('horario.docentes.usuario', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%')
                  ->orWhere('apellido_paterno', 'like', '%' . $search . '%');
            });
        });
    }

    // Aplicar el filtro de estado si se ha seleccionado
    if ($estado) {
        $query->whereIn('estado', (array)$estado);
    }

    // Paginación
    $solicitudes = $query->paginate($perPage);

    // Formatear los datos de la respuesta
    $result = $solicitudes->map(function ($solicitud) {
        // Obtener datos del estudiante
        $estudiante = $solicitud->estudiante;
        $usuarioEstudiante = $estudiante->usuario;

        // Componer el nombre completo del estudiante
        $nombreCompletoEstudiante = $usuarioEstudiante->nombre . ' ' . 
            $usuarioEstudiante->apellido_paterno . ' ' . 
            $usuarioEstudiante->apellido_materno;

        // Obtener el profesor
        $profesor = isset($solicitud->horario->docentes->first()->usuario)
            ? $solicitud->horario->docentes->first()->usuario->nombre . ' ' . 
              $solicitud->horario->docentes->first()->usuario->apellido_paterno
            : 'Sin Profesor';

        // Obtener el curso
        $cursoNombre = $solicitud->horario->curso->nombre;
        
        // Obtener el código de horario
        $codigoHorario = $solicitud->horario->codigo;

        // Devolver la información formateada
        return [
            'id' => $solicitud->id,  // ID de la solicitud
            'codigo_alumno' => $estudiante->codigoEstudiante,  // Código del alumno
            'nombre_alumno' => $nombreCompletoEstudiante,  // Nombre del alumno
            'ultima_modificacion' => Carbon::parse($solicitud->updated_at)->format('d-m-Y'),  // Última modificación
            'curso' => $cursoNombre,  // Nombre del curso
            'codigo_horario' => $codigoHorario,  // Código del horario
            'estado' => $solicitud->estado,  // Estado de la solicitud
        ];
    });

    // Responder con los datos formateados y la paginación
    return response()->json([
        'data' => $result,
        'pagination' => [
            'total' => $solicitudes->total(), // Total de filas
            'current_page' => $solicitudes->currentPage(),
            'last_page' => $solicitudes->lastPage(),
            'per_page' => $solicitudes->perPage(),
        ],
    ]);
}

public function rechazarCarta($id, Request $request)
    {
        // Validación del motivo de rechazo
        $validated = $request->validate([
            'motivo_rechazo' => 'required|string|min:10',  // El motivo debe ser obligatorio y mínimo 10 caracteres
        ]);

        // Buscar la carta de presentación por ID
        $carta = CartaPresentacionSolicitud::find($id);

        if (!$carta) {
            return response()->json(['message' => 'Carta de presentación no encontrada.'], 404);
        }

        // Actualizar el estado de la carta a "Rechazada" y guardar el motivo de rechazo
        $carta->estado = 'Rechazado';
        $carta->motivo_rechazo = $validated['motivo_rechazo'];
        $carta->save();

        // Respuesta exitosa
        return response()->json([
            'message' => 'Carta de presentación rechazada correctamente.',
            'carta' => $carta
        ], 200);
    }

    public function aprobarCartaSecretaria($id, Request $request)
    {
        // Buscar la carta de presentación por ID
        $carta = CartaPresentacionSolicitud::find($id);

        if (!$carta) {
            return response()->json(['message' => 'Carta de presentación no encontrada.'], 404);
        }

        // Verificar si el estado es "Pendiente Secretario"
        if ($carta->estado !== 'Pendiente Secretaria') {
            return response()->json([
                'message' => 'La carta no puede ser aprobada porque no está en el estado "Pendiente Secretario".'
            ], 400);
        }

        // Cambiar el estado a "Pendiente Firma DC"
        $carta->estado = 'Pendiente Firma DC';
        $carta->save();

        // Respuesta exitosa
        return response()->json([
            'message' => 'Carta de presentación aprobada correctamente.',
            'carta' => $carta
        ], 200);
    }

    public function aprobarCartaDirector($id)
    {
        // Buscar la carta de presentación por ID
        $carta = CartaPresentacionSolicitud::find($id);

        if (!$carta) {
            return response()->json(['message' => 'Carta de presentación no encontrada.'], 404);
        }

        // Verificar si el estado es "Pendiente Secretario"
        if ($carta->estado !== 'Pendiente Firma DC') {
            return response()->json([
                'message' => 'La carta no puede ser aprobada porque no está en el estado "Pendiente Director".'
            ], 400);
        }

        // Cambiar el estado a "Aprobado"
        $carta->estado = 'Aprobado';
        $carta->save();

        // Respuesta exitosa
        return response()->json([
            'message' => 'Carta de presentación aprobada correctamente.',
            'carta' => $carta
        ], 200);
    }

    public function getByProfesor(Request $request, $profesorId)
{
    // Recoger los filtros de búsqueda y estado
    $search = $request->input('search', ''); // Campo de búsqueda
    $estado = $request->input('estado', null); // Estado para filtrar
    $perPage = $request->input('per_Page', 10); // Cantidad de elementos por página

    // Comenzar la consulta
    $query = CartaPresentacionSolicitud::with([
        'estudiante.usuario', // Cargar el usuario del estudiante
        'horario', // Cargar los horarios
        'horario.docentes.usuario:id,nombre,apellido_paterno', // Cargar los docentes del horario
        'horario.curso', // Cargar el curso asociado al horario
    ])
    // Filtrar por los horarios asociados a este profesor
    ->whereHas('horario.docentes', function($q) use ($profesorId) {
        $q->where('docente_id', $profesorId);
    });

    // Aplicar el filtro de búsqueda si el campo no está vacío
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('horario', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%');
            })
            ->orWhereHas('horario.curso', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%');
            })
            ->orWhereHas('horario.docentes.usuario', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%')
                  ->orWhere('apellido_paterno', 'like', '%' . $search . '%');
            });
        });
    }

    // Aplicar el filtro de estado si se ha seleccionado
    if ($estado) {
        $query->whereIn('estado', (array)$estado); // Asegura que el estado sea un array
    }

    // Paginación
    $solicitudes = $query->paginate($perPage);

    // Formatear los resultados
    $result = $solicitudes->map(function ($solicitud) {
        return [
            'id' => $solicitud->id, // Primer campo es id
            'profesor' => isset($solicitud->horario->docentes->first()->usuario)
                ? $solicitud->horario->docentes->first()->usuario->nombre . ' ' . $solicitud->horario->docentes->first()->usuario->apellido_paterno
                : 'Sin Profesor', // Profesor
            'curso' => $solicitud->horario->curso->nombre, // Curso
            'horario' => $solicitud->horario->codigo, // Código del horario
            'ultimaModificacion' => Carbon::parse($solicitud->updated_at)->format('d-m-Y'), // Última modificación
            'estado' => $solicitud->estado, // Estado
        ];
    });

    // Retornar la respuesta con la paginación
    return response()->json([
        'data' => $result,
        'pagination' => [
            'total' => $solicitudes->total(), // Total de filas
            'current_page' => $solicitudes->currentPage(),
            'last_page' => $solicitudes->lastPage(),
            'per_page' => $solicitudes->perPage(),
        ],
    ]);
}

public function solicitarActividades($id, Request $request)
{
    // Buscar la carta de presentación por ID
    $carta = CartaPresentacionSolicitud::find($id);

    if (!$carta) {
        return response()->json(['message' => 'Carta de presentación no encontrada.'], 404);
    }

    // Verificar si el estado es "Pendiente Secretaria"
    if ($carta->estado !== 'Pendiente Secretaria') {
        return response()->json([
            'message' => 'La carta no puede ser actualizada porque no está en el estado "Pendiente Secretaria".'
        ], 400);
    }

    // Cambiar el estado a "Pendiente de Actividades"
    $carta->estado = 'Pendiente de Actividades';
    $carta->save();

    // Respuesta exitosa
    return response()->json([
        'message' => 'Solicitud de actividades enviada correctamente.',
        'carta' => $carta
    ], 200);
}

    public function aprobarPorHorario($horario_id)
    {
        // Buscar todas las solicitudes de carta de presentación con el estado "Pendiente Actividades" y el horario_id proporcionado
        $cartas = CartaPresentacionSolicitud::where('horario_id', $horario_id)
                                            ->where('estado', 'Pendiente de Actividades')
                                            ->get();

        // Verificar si se encontraron cartas
        if ($cartas->isEmpty()) {
            return response()->json(['message' => 'No se encontraron cartas de presentación con ese horario y estado.'], 404);
        }

        // Cambiar el estado de cada carta a "Pendiente Firma DC"
        foreach ($cartas as $carta) {
            $carta->estado = 'Pendiente Firma DC';
            $carta->save();  // Guardar cada carta con el nuevo estado
        }

        // Respuesta exitosa
        return response()->json([
            'message' => 'Las cartas de presentación se han actualizado correctamente.',
            'cartas' => $cartas
        ], 200);
    }

    public function subirArchivo(Request $request, $id)
    {
        try {
            // Validar el archivo recibido
            $request->validate([
                'file' => 'required|file|mimes:pdf|max:10240', // Limitar a archivos PDF de máximo 10MB
            ]);

            // Buscar la solicitud de carta de presentación
            $carta = CartaPresentacionSolicitud::findOrFail($id);

            if ($carta->estado !== 'Pendiente Firma DC') {
                return response()->json(['message' => 'La carta no está en un estado que permita la subida del archivo.'], 403);
            }

            // Subir el archivo al sistema y crear un registro en la tabla `files`
            $file = $request->file('file');
            $path = 'carta_presentacion/' . uniqid() . '_' . $file->getClientOriginalName();
            Storage::disk('s3')->put($path, file_get_contents($file)); // Uso del facade Storage

            $fileRecord = File::create([
                'name' => $file->getClientOriginalName(),
                'file_type' => 'document',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $path,
                'url' => Storage::disk('s3')->url($path),
            ]);

            // Asociar el archivo a la carta de presentación
            $carta->file_id = $fileRecord->id;
            $carta->save();

            return response()->json([
                'message' => 'Archivo subido y asociado correctamente.',
                'carta' => $carta,
                'file' => $fileRecord,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al subir el archivo: ' . $e->getMessage()], 500);
        }
    }


    public function descargarArchivo($id)
    {
        try {
            // Buscar la solicitud por su ID
            $solicitud = CartaPresentacionSolicitud::with('file')->findOrFail($id);

            if ($solicitud->estado !== 'Aprobado') {
                return response()->json(['message' => 'La carta no está en un estado que permita la descarga del archivo.'], 403);
            }
            // Verificar si hay un archivo asociado
            if (!$solicitud->file) {
                return response()->json(['message' => 'No hay un archivo asociado a esta solicitud.'], 404);
            }

            // Obtener la ruta del archivo en S3
            $fileRecord = $solicitud->file;
            $filePath = $fileRecord->path;

            // Verificar si el archivo existe en S3
            if (!Storage::disk('s3')->exists($filePath)) {
                return response()->json(['message' => 'El archivo no existe en el sistema.'], 404);
            }

            // Descargar el archivo desde S3
            $fileContent = Storage::disk('s3')->get($filePath);

            // Retornar la respuesta con el archivo para su descarga
            return response($fileContent, 200)
                ->header('Content-Type', $fileRecord->mime_type)
                ->header('Content-Disposition', 'attachment; filename="' . $fileRecord->name . '"');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al descargar el archivo: ' . $e->getMessage()], 500);
        }
    }

}