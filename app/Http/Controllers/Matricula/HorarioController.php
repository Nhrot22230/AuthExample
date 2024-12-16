<?php

namespace App\Http\Controllers\Matricula;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Matricula\Horario;
use App\Models\Universidad\Semestre;
use App\Models\Delegados\Delegado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class HorarioController extends Controller
{
    //
    public function obtenerCursosEstudiante($estudianteId)
    {
        $semestre_id = Semestre::where('estado', 'activo')->orderByDesc('fecha_inicio')->first()->id;

        $horarios = Horario::where('semestre_id', $semestre_id)
            ->whereHas('estudiantes', function ($query) use ($estudianteId) {
                $query->where('estudiante_id', $estudianteId);
            })
            ->whereHas('encuestas', function ($query) {
                $query->where('disponible', true); // Filtrar encuestas activas basadas en el campo 'disponible'
            })
            ->with(['curso', 'horarioEstudiantes.horarioEstudianteJps', 'docentes.usuario']) // Cargar docentes y usuario
            ->get();

        // Procesar los horarios para estructurar los datos de los cursos y los JPs evaluados
        $cursos = $horarios->map(function ($horario) use ($estudianteId) {
            $jpsEvaluados = $horario->horarioEstudiantes
                ->where('estudiante_id', $estudianteId) // Filtra el estudiante específico
                ->flatMap(function ($horarioEstudiante) {
                    return $horarioEstudiante->horarioEstudianteJps->where('encuestaJP', true);
                })
                ->count();

            // Obtener el nombre completo del primer docente asociado al horario
            $docente = $horario->docentes->first();
            $nombreDocente = $docente ? $docente->usuario->nombre . ' ' . $docente->usuario->apellido_paterno . ' ' . $docente->usuario->apellido_materno : 'Sin docente asignado';

            return [
                'horario_id' => $horario->id,
                'curso_id' => $horario->curso->id,
                'curso_nombre' => $horario->curso->nombre,
                'jps_evaluados' => $jpsEvaluados,
                'nombre_docente' => $nombreDocente,
            ];
        });

        return response()->json([
            'cursos' => $cursos,
        ]);
    }
    public function obtenerJps($horarioId)
    {
        $horario = Horario::with([
            'curso',
            'horarioEstudiantes.horarioEstudianteJps',
            'jefePracticas.usuario'
        ])->findOrFail($horarioId);

        // Mapear la información de los jefes de práctica asociados al horario
        $jps = $horario->jefePracticas->map(function ($jp) use ($horario) {
            // Obtener el estado de la encuesta y el ID de la encuesta asociada al JP en este horario
            $estadoEncuesta = $horario->horarioEstudiantes
                ->flatMap(function ($horarioEstudiante) use ($jp) {
                    return $horarioEstudiante->horarioEstudianteJps
                        ->where('jp_horario_id', $jp->id)
                        ->pluck('encuestaJP'); // Obtener el estado de la encuesta
                })
                ->first();

            $encuestaId = $horario->encuestas
                ->where('tipo_encuesta', 'jefe_practica') // Filtrar por tipo de encuesta
                ->first()?->id; // Obtener el ID de la encuesta de tipo 'jefe_practica'

            return [
                'id' => $jp->id,
                'nombre' => $jp->usuario->nombre,
                'apellido_paterno' => $jp->usuario->apellido_paterno,
                'apellido_materno' => $jp->usuario->apellido_materno,
                'imagen' => $jp->usuario->picture,
                'estado' => $estadoEncuesta,
                'encuesta_id' => $encuestaId, // Incluir el ID de la encuesta de tipo 'jefe_practica'
            ];
        });
        $detalleHorario = [
            'curso' => [
                'id' => $horario->curso->id,
                'nombre' => $horario->curso->nombre,
            ],
            'jefes_practica' => $jps
        ];

        return response()->json($detalleHorario);
    }

    public function obtenerEncuestasDocentesEstudiante($estudianteId)
    {
        $semestre = Semestre::where('estado', 'activo')->orderByDesc('fecha_inicio')->first();
        if (!$semestre) {
            return response()->json(['message' => 'No hay un semestre activo'], 404);
        }
        $semestre_id = $semestre->id;

        $horarios = Horario::where('semestre_id', $semestre_id)
            ->whereHas('horarioEstudiantes', function ($query) use ($estudianteId) {
                $query->where('estudiante_id', $estudianteId);
            })
            ->with([
                'curso',
                'docentes.usuario', // Cargar la relación de docentes y usuarios
                'horarioEstudiantes' => function ($query) use ($estudianteId) {
                    $query->where('estudiante_id', $estudianteId)
                        ->select('horario_id', 'estudiante_id', 'encuestaDocente');
                },
                'encuestas' => function ($query) {
                $query->where('tipo_encuesta', 'docente')
                      ->where('disponible', true); // Filtrar solo encuestas disponibles
            } 
            ])
            ->get()
            ->filter(function ($horario) {
                return $horario->encuestas->isNotEmpty(); // Retener solo horarios con encuestas activas
            });

        $cursos = $horarios->map(function ($horario) {
            $estadoEncuesta = optional($horario->horarioEstudiantes->first())->encuestaDocente;

            $encuestas = $horario->encuestas->map(function ($encuesta) {
                return $encuesta->id;
            });

            // Obtener el nombre del primer docente (si existe)
            $docente = $horario->docentes->first();
            $nombreDocente = $docente ? $docente->usuario->nombre . ' ' . $docente->usuario->apellido_paterno . ' ' . $docente->usuario->apellido_materno : null;

            return [
                'horario_id' => $horario->id,
                'curso_id' => $horario->curso->id,
                'docente_nombre' => $nombreDocente,
                'curso_nombre' => $horario->curso->nombre,
                'estado_encuesta' => $estadoEncuesta,
                'encuestas' => $encuestas,
            ];
        })->values();

        return response()->json([
            'cursos' => $cursos,
        ]);
    }

    public function obtenerDelegado(Request $request)
    {
        // Validar el ID del horario
        $validated = $request->validate([
            'id_horario' => 'required|exists:horarios,id',
        ]);

        try {
            $horarioId = $validated['id_horario'];

            // Obtener el delegado asociado al horario
            $delegado = Delegado::where('horario_id', $horarioId)
                ->join('estudiantes', 'delegados.estudiante_id', '=', 'estudiantes.id') // Unir con estudiantes
                ->join('usuarios', 'estudiantes.usuario_id', '=', 'usuarios.id') // Unir con usuarios
                ->select(
                    'delegados.id as delegado_id',
                    'delegados.horario_id',
                    'delegados.estudiante_id',
                    DB::raw("CONCAT(usuarios.nombre, ' ', usuarios.apellido_paterno, ' ', usuarios.apellido_materno) as delegado_nombre"),
                    'usuarios.email as delegado_email'
                )
                ->first();

            if (!$delegado) {
                return response()->json([
                    'message' => 'No hay delegado asignado para este horario.',
                    'delegado' => null
                ], 200);
            }

            // Devolver los datos del delegado
            return response()->json([
                'message' => 'Delegado encontrado.',
                'delegado' => $delegado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el delegado.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function obtenerHorariosConJefes(Request $request)
    {
        // Validar que el "id_curso" fue enviado
        $validated = $request->validate([
            'id_curso' => 'required|exists:cursos,id', // id_curso mapeado a la columna "id" de la tabla cursos
        ]);

        try {
            $cursoId = $validated['id_curso'];

            // Obtener los horarios relacionados al curso junto con los usuarios asignados (jefes de práctica)
            $horarios = Horario::where('curso_id', $cursoId)
                ->with(['jefesPractica' => function ($query) {
                    $query->join('usuarios', 'jp_horario.usuario_id', '=', 'usuarios.id')
                        ->select(
                            'jp_horario.horario_id',
                            'usuarios.id as usuario_id',
                            'usuarios.nombre',
                            'usuarios.apellido_paterno',
                            'usuarios.apellido_materno',
                            'usuarios.email'
                        );
                }])
                ->get(['id', 'nombre', 'codigo']); // Incluye los campos del horario

            // Verificar si hay horarios
            if ($horarios->isEmpty()) {
                return response()->json(['message' => 'No se encontraron horarios para este curso.'], 404);
            }

            // Formatear la respuesta
            $horariosConJefes = $horarios->map(function ($horario) {
                return [
                    'id' => $horario->id,
                    'nombre' => $horario->nombre,
                    'codigo' => $horario->codigo,
                    'jefes' => $horario->jefesPractica->map(function ($jefe) {
                        return [
                            'id' => $jefe->usuario_id,
                            'nombre' => "{$jefe->nombre} {$jefe->apellido_paterno} {$jefe->apellido_materno}",
                            'email' => $jefe->email,
                        ];
                    }),
                ];
            });

            return response()->json($horariosConJefes, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los horarios.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarJefePractica(Request $request)
    {
        // Validar los datos proporcionados en la solicitud
        $validated = $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'usuario_id' => 'required|exists:usuarios,id',
        ]);

        try {
            // Eliminar la fila de la tabla jp_horario
            $deleted = DB::table('jp_horario')
                ->where('horario_id', $validated['horario_id'])
                ->where('usuario_id', $validated['usuario_id'])
                ->delete();

            if ($deleted) {
                return response()->json(['message' => 'Jefe de práctica eliminado correctamente.'], 200);
            } else {
                return response()->json(['message' => 'No se encontró la combinación de horario y usuario.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar jefe de práctica.', 'error' => $e->getMessage()], 500);
        }
    }

    public function listarUsuariosEstudiantesYDocentes(Request $request)
    {
        try {
            // Obtener parámetros de búsqueda, tipo y horario_id
            $search = $request->input('search', ''); // Búsqueda (por nombre, código o especialidad)
            $tipo = $request->input('tipo', ''); // Tipo (Docente, Estudiante o vacío para ambos)
            $horarioId = $request->input('horario_id'); // ID del horario

            // Obtener IDs de usuarios ya relacionados al horario en jp_horario
            $usuariosRelacionados = DB::table('jp_horario')
                ->where('horario_id', $horarioId)
                ->pluck('usuario_id')
                ->toArray();

            // Consulta base para estudiantes
            $estudiantesQuery = DB::table('usuarios')
                ->join('estudiantes', 'usuarios.id', '=', 'estudiantes.usuario_id')
                ->join('especialidades', 'estudiantes.especialidad_id', '=', 'especialidades.id')
                ->select(
                    'usuarios.id as id',
                    DB::raw("CONCAT(usuarios.nombre, ' ', usuarios.apellido_paterno, ' ', usuarios.apellido_materno) as nombre"),
                    'usuarios.email',
                    'estudiantes.codigoEstudiante as codigo',
                    'especialidades.nombre as especialidad',
                    DB::raw("'Estudiante' as tipo") // Agregar el tipo "Estudiante"
                )
                ->whereNotIn('usuarios.id', $usuariosRelacionados); // Excluir usuarios ya relacionados

            // Consulta base para docentes
            $docentesQuery = DB::table('usuarios')
                ->join('docentes', 'usuarios.id', '=', 'docentes.usuario_id')
                ->join('especialidades', 'docentes.especialidad_id', '=', 'especialidades.id')
                ->select(
                    'usuarios.id as id',
                    DB::raw("CONCAT(usuarios.nombre, ' ', usuarios.apellido_paterno, ' ', usuarios.apellido_materno) as nombre"),
                    'usuarios.email',
                    'docentes.codigoDocente as codigo',
                    'especialidades.nombre as especialidad',
                    DB::raw("'Docente' as tipo") // Agregar el tipo "Docente"
                )
                ->whereNotIn('usuarios.id', $usuariosRelacionados); // Excluir usuarios ya relacionados

            // Filtro por búsqueda (nombre, código o especialidad)
            if (!empty($search)) {
                $estudiantesQuery->where(function ($query) use ($search) {
                    $query->where('usuarios.nombre', 'LIKE', "%$search%")
                        ->orWhere('usuarios.apellido_paterno', 'LIKE', "%$search%")
                        ->orWhere('usuarios.apellido_materno', 'LIKE', "%$search%")
                        ->orWhere('estudiantes.codigoEstudiante', 'LIKE', "%$search%")
                        ->orWhere('especialidades.nombre', 'LIKE', "%$search%");
                });

                $docentesQuery->where(function ($query) use ($search) {
                    $query->where('usuarios.nombre', 'LIKE', "%$search%")
                        ->orWhere('usuarios.apellido_paterno', 'LIKE', "%$search%")
                        ->orWhere('usuarios.apellido_materno', 'LIKE', "%$search%")
                        ->orWhere('docentes.codigoDocente', 'LIKE', "%$search%")
                        ->orWhere('especialidades.nombre', 'LIKE', "%$search%");
                });
            }

            // Filtro por tipo
            if ($tipo === 'Estudiante') {
                $usuarios = $estudiantesQuery->get(); // Solo estudiantes
            } elseif ($tipo === 'Docente') {
                $usuarios = $docentesQuery->get(); // Solo docentes
            } else {
                // Ambos tipos
                $estudiantes = $estudiantesQuery->get();
                $docentes = $docentesQuery->get();
                $usuarios = $estudiantes->merge($docentes);
            }

            return response()->json($usuarios, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener usuarios.', 'error' => $e->getMessage()], 500);
        }
    }

    

    public function agregarJefePracticaAHorario(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'horario_id' => 'required|exists:horarios,id',
            'usuario_id' => 'required|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validación fallida.',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            // Insertar una nueva fila en la tabla jp_horario
            DB::table('jp_horario')->insert([
                'horario_id' => $request->input('horario_id'),
                'usuario_id' => $request->input('usuario_id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Jefe de práctica agregado exitosamente.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al agregar el jefe de práctica.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
