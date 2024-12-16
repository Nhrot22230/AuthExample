<?php

namespace App\Http\Controllers\Solicitudes;

use App\Http\Controllers\Controller;
use App\Models\Matricula\Horario;
use App\Models\Matricula\HorarioEstudianteJp;
use App\Models\Solicitudes\MatriculaAdicional;
use App\Models\Universidad\Semestre;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Universidad\Curso;
use App\Models\Usuarios\Estudiante;

// Asegúrate de importar tu modelo

class MatriculaAdicionalController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'estudiante_id' => 'required|integer',
            'especialidad_id' => 'required|integer',
            'motivo' => 'required|string',
            'justificacion' => 'required|string',
            'curso_id' => 'required|integer',
            'horario_id' => 'required|integer',
            'motivo_rechazo' => 'nullable|string',
        ]);

        // Si la validación falla, retorna un mensaje de error
        if ($validator->fails()) {
            // Crear un string con los campos requeridos
            $missingFields = implode(', ', array_keys($validator->errors()->toArray()));
            return response()->json([
                'message' => 'Los siguientes campos son obligatorios: ' . $missingFields
            ], 400);
        }

        // Crear una nueva matrícula adicional
        $matricula = MatriculaAdicional::create([
            'estudiante_id' => $request->estudiante_id,
            'especialidad_id' => $request->especialidad_id,
            'motivo' => $request->motivo,
            'justificacion' => $request->justificacion,
            'estado' => 'Pendiente DC', // O cualquier otro valor predeterminado que desees
            'motivo_rechazo' => $request->motivo_rechazo,
            'curso_id' => $request->curso_id,
            'horario_id' => $request->horario_id,
        ]);

        // Retornar la respuesta
        return response()->json([
            'message' => 'Matrícula adicional creada con éxito.',
            'matricula' => $matricula,
        ], 201);
    }



    public function getAll()
    {
        // Carga ansiosa para obtener los datos relacionados
        $matriculas = MatriculaAdicional::with(['estudiante.usuario', 'especialidad'])->get();
        return response()->json($matriculas);
    }

    public function getByEspecialidad(Request $request, $id)
    {
        // Recoger los filtros de búsqueda y estado
        $search = $request->input('search', ''); // Campo de búsqueda
        $estado = $request->input('estado', null); // Estado para filtrar
        $perPage = $request->input('per_Page', 10); // Cantidad de elementos por página

        // Comenzar la consulta
        $query = MatriculaAdicional::with([
            'estudiante.usuario',
            'especialidad',
            'curso',
            'horario',
            'horario.docentes.usuario:id,nombre,apellido_paterno'
        ])
        ->where('especialidad_id', $id);

        // Aplicar el filtro de búsqueda si el campo no está vacío
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('estudiante.usuario', function ($subQuery) use ($search) {
                    $subQuery->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('apellido_paterno', 'like', '%' . $search . '%');
                })
                ->orWhereHas('curso', function ($subQuery) use ($search) {
                    $subQuery->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('cod_curso', 'like', '%' . $search . '%');
                });
            });
        }

        // Aplicar el filtro de estado si se ha seleccionado
        if ($estado) {
            $query->whereIn('estado', (array)$estado);
        }

        // Paginación
        $matriculas = $query->paginate($perPage);

        $result = $matriculas->map(function ($matricula) {
            $estudiante = $matricula->estudiante;
            return [
                'id' => $matricula->id,
                'codigo' => $estudiante->codigoEstudiante,
                'nombres' => $estudiante->usuario->nombre . ' ' . $estudiante->usuario->apellido_paterno . ' ' . $estudiante->usuario->apellido_materno,
                'ultimaModificacion' => Carbon::parse($matricula->updated_at)->format('d-m-Y'),
                'curso' => $matricula->curso->nombre,
                'horario' => $matricula->horario->codigo,
                'estado' => $matricula->estado,
            ];
        });

        return response()->json([
            'data' => $result,
            'pagination' => [
                'total' => $matriculas->total(),
                'current_page' => $matriculas->currentPage(),
                'last_page' => $matriculas->lastPage(),
                'per_page' => $matriculas->perPage(),
            ],
        ]);
    }

    public function getByEstudiante(Request $request, $estudianteId)
    {
        // Recoger los filtros de búsqueda y estado
        $search = $request->input('search', ''); // Campo de búsqueda
        $estado = $request->input('estado', null); // Estado para filtrar
        $perPage = $request->input('per_Page', 10); // Cantidad de elementos por página

        // Comenzar la consulta
        $query = MatriculaAdicional::with([
            'estudiante.usuario',
            'especialidad',
            'curso',
            'horario',
            'horario.docentes.usuario:id,nombre,apellido_paterno',
        ])
        ->where('estudiante_id', $estudianteId);

        // Aplicar el filtro de búsqueda si el campo no está vacío
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('curso', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('cod_curso', 'like', '%' . $search . '%');
                })
                ->orWhereHas('horario', function ($q) use ($search) {
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
        $matriculas = $query->paginate($perPage);

        $result = $matriculas->map(function ($matricula) {
            return [
                'id' => $matricula->id,
                'clave' => $matricula->curso->cod_curso,
                'curso' => $matricula->curso->nombre,
                'horario' => $matricula->horario->codigo,
                'profesor' => isset($matricula->horario->docentes->first()->usuario)
                    ? $matricula->horario->docentes->first()->usuario->nombre . ' ' . $matricula->horario->docentes->first()->usuario->apellido_paterno
                    : 'Sin Profesor',
                'ultimaModificacion' => Carbon::parse($matricula->updated_at)->format('d-m-Y'),
                'estado' => $matricula->estado,
            ];
        });

        return response()->json([
            'data' => $result,
            'pagination' => [
                'total' => $matriculas->total(), // Total de filas
                'current_page' => $matriculas->currentPage(),
                'last_page' => $matriculas->lastPage(),
                'per_page' => $matriculas->perPage(),
            ],
        ]);
    }


    public function getByFacultad(Request $request, $facultadId)
    {
        // Recoger los filtros de búsqueda y estado
        $search = $request->input('search', ''); // Campo de búsqueda
        $estado = $request->input('estado', null); // Estado para filtrar
        $perPage = $request->input('per_Page', 10); // Cantidad de elementos por página

        // Comenzar la consulta
        $query = MatriculaAdicional::with([
            'estudiante.usuario',
            'especialidad',
            'curso',
            'horario',
            'horario.docentes.usuario',
        ])
        ->whereHas('especialidad', function ($query) use ($facultadId) {
            $query->where('facultad_id', $facultadId);
        });

        // Aplicar el filtro de búsqueda si el campo no está vacío
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('estudiante.usuario', function ($subQuery) use ($search) {
                    $subQuery->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('apellido_paterno', 'like', '%' . $search . '%');
                })
                ->orWhereHas('curso', function ($subQuery) use ($search) {
                    $subQuery->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('cod_curso', 'like', '%' . $search . '%');
                });
            });
        }

        // Aplicar el filtro de estado si se ha seleccionado
        if ($estado) {
            $query->whereIn('estado', (array)$estado);
        }

        // Paginación
        $matriculas = $query->paginate($perPage);

        $result = $matriculas->map(function ($matricula) {
            return [
                'id' => $matricula->id,
                'codigo' => $matricula->estudiante->codigoEstudiante,
                'nombres' => $matricula->estudiante->usuario->nombre . ' ' . $matricula->estudiante->usuario->apellido_paterno,
                'ultimaModificacion' => $matricula->updated_at->format('d/m/Y'),
                'curso' => $matricula->curso->nombre,
                'especialidad' => $matricula->especialidad->nombre,
                'estado' => $matricula->estado,
            ];
        });

        return response()->json([
            'data' => $result,
            'pagination' => [
                'total' => $matriculas->total(),
                'current_page' => $matriculas->currentPage(),
                'last_page' => $matriculas->lastPage(),
                'per_page' => $matriculas->perPage(),
            ],
        ]);
    }

    public function getHorariosByCurso(Request $request, $cursoId)
    {
        // Obtener el semestre activo
        $semestreActivo = Semestre::where('estado', 'activo')->orderByDesc('fecha_inicio')->first();

        if (!$semestreActivo) {
            return response()->json(['message' => 'No hay semestre activo'], 404);
        }

        // Obtener solo los IDs y nombres de los horarios del curso que pertenecen al semestre activo
        $horarios = Horario::where('curso_id', $cursoId)
            ->where('semestre_id', $semestreActivo->id)
            ->select('id', 'codigo') // Solo selecciona el id y el código
            ->get();

        return response()->json($horarios);
    }

    public function getById($id)
    {
        // Obtener la matrícula adicional por su ID
        $matricula = MatriculaAdicional::with([
            'estudiante.usuario',
            'curso',
            'horario' // Asegúrate de incluir el horario si necesitas información de él
        ])->find($id);

        // Verificar si se encontró la matrícula
        if (!$matricula) {
            return response()->json(['message' => 'Matrícula adicional no encontrada'], 404);
        }

        // Preparar la respuesta
        $response = [
            'id' => $matricula->id,
            'codigoEstudiante' => $matricula->estudiante->codigoEstudiante,
            'nombreEstudiante' => $matricula->estudiante->usuario->nombre . ' ' .
                                $matricula->estudiante->usuario->apellido_paterno . ' ' .
                                $matricula->estudiante->usuario->apellido_materno,
            'correoEstudiante' => $matricula->estudiante->usuario->email,
            'codigoHorario' => $matricula->horario->codigo,
            'motivo' => $matricula->motivo,
            'justificacion' => $matricula->justificacion,
            'claveCurso' => $matricula->curso->id,
            'xd'=>$matricula->curso->cod_curso,
            'motivoRechazo' => $matricula->motivo_rechazo,
            'estado' => $matricula->estado, // Agregar estado
        ];

        return response()->json($response);
    }

    public function rechazar(Request $request, $id)
    {
        // Validar la solicitud
        $request->validate([
            'motivo_rechazo' => 'required|string|max:255',
        ]);

        // Encontrar la matrícula
        $matricula = MatriculaAdicional::findOrFail($id);

        // Cambiar el estado y actualizar el motivo de rechazo
        $matricula->estado = 'Rechazado';
        $matricula->motivo_rechazo = $request->motivo_rechazo;

        // Guardar los cambios
        $matricula->save();

        return response()->json([
            'message' => 'Matrícula rechazada con éxito.',
            'matricula' => $matricula,
        ]);
    }

    public function aprobarPorDC($id)
    {
        $matricula = MatriculaAdicional::findOrFail($id);

        // Verifica si el estado es 'Pendiente DC'
        if ($matricula->estado !== 'Pendiente DC') {
            return response()->json(['message' => 'El estado de la matrícula no es válido para esta acción.'], 400);
        }

        // Cambia el estado a 'Pendiente SA'
        $matricula->estado = 'Pendiente SA';
        $matricula->save();

        return response()->json(['message' => 'Matrícula actualizada a Pendiente SA.']);
    }

    public function aprobarPorSA($id)
    {
        $matricula = MatriculaAdicional::findOrFail($id);

        // Verifica si el estado es 'Pendiente SA'
        if ($matricula->estado !== 'Pendiente SA') {
            return response()->json(['message' => 'El estado de la matrícula no es válido para esta acción.'], 400);
        }

        // Cambia el estado a 'Aprobado'
        $matricula->estado = 'Aprobado';
        $matricula->save();

        // Extraer datos necesarios para matricular al estudiante
        $codigoCurso = $matricula->curso->cod_curso; // Asume que MatriculaAdicional tiene relación con Curso
        $codigoHorario = $matricula->horario->codigo; // Asume que MatriculaAdicional tiene relación con Horario
        $codigoAlumno = $matricula->estudiante->codigoEstudiante; // Asume que MatriculaAdicional tiene relación con Estudiante

        // Validar que los datos del curso y horario son consistentes
        $curso = Curso::where('cod_curso', $codigoCurso)->first();
        $horario = Horario::where('codigo', $codigoHorario)
            ->where('curso_id', $curso->id)
            ->first();
        $alumno = Estudiante::where('codigoEstudiante', $codigoAlumno)->first();

        if (!$curso || !$horario || !$alumno) {
            return response()->json([
                'message' => 'Error al validar los datos de la matrícula. Verifique curso, horario y estudiante.',
            ], 400);
        }

        // Verificar si ya está inscrito
        if ($horario->estudiantes()->where('estudiante_id', $alumno->id)->exists()) {
            return response()->json([
                'message' => 'El alumno ya está inscrito en este horario',
            ], 400);
        }

        // Inscribir al alumno en el horario
        $horario->estudiantes()->attach($alumno->id);

        // Verificar si existen jefes de práctica asignados a este horario
        $jefesPractica = $horario->jefesPractica; // Obtener los jefes de práctica del horario

        if ($jefesPractica->isNotEmpty()) {
            // Crear el registro en la tabla HorarioEstudianteJp para cada jefe de práctica
            foreach ($jefesPractica as $jefePractica) {
                // Asegurarse de que el alumno esté asociado al horario en la tabla intermedia 'estudiante_horario'
                $estudianteHorario = $alumno->horarios()->where('horario_id', $horario->id)->first();

                if ($estudianteHorario) {
                    HorarioEstudianteJp::create([
                        'estudiante_horario_id' => $estudianteHorario->id, // ID de la relación entre el estudiante y el horario
                        'jp_horario_id' => $jefePractica->id, // ID del jefe de práctica
                        'encuestaJP' => 0, // Establecer valor predeterminado de encuesta
                    ]);
                }
            }
        }
        
        return response()->json(['message' => 'Matrícula aprobada y alumno inscrito correctamente en el curso.']);
    }

    public function buscarCursosMat(Request $request)
    {
        $query = $request->input('query'); // El texto que busca el usuario
        $estudianteId = $request->input('estudianteId');

        if (!$estudianteId) {
            return response()->json(['message' => 'No se encontró al estudiante.'], 400);
        }

        // Buscar cursos por nombre o código, excluyendo cursos con horarios donde el estudiante esté matriculado
        $cursos = Curso::where(function ($q) use ($query) {
            $q->where('cod_curso', 'like', '%' . $query . '%')
            ->orWhere('nombre', 'like', '%' . $query . '%');
            })
            ->whereDoesntHave('horarios', function ($horarioQuery) use ($estudianteId) {
                $horarioQuery->whereHas('horarioEstudiantes', function ($horarioEstudiantesQuery) use ($estudianteId) {
                    // Excluir horarios donde el estudiante ya está matriculado
                    $horarioEstudiantesQuery->where('estudiante_id', $estudianteId);
                });
            })
            ->whereHas('horarios', function ($horarioQuery) {
                $horarioQuery->where('oculto', 0) // Horarios visibles
                    ->whereHas('semestre', function ($semestreQuery) {
                        $semestreQuery->where('estado', 'activo'); // Semestre activo
                    });
            })
            ->get();

        // Concatenar el código con el nombre del curso
        $resultados = $cursos->map(function ($curso) {
            return [
                'id' => $curso->id,
                'label' => $curso->cod_curso . ' - ' . $curso->nombre // Concatenamos código con nombre
            ];
        });

        // Devolvemos los resultados como respuesta JSON
        return response()->json($resultados);
    }
}
