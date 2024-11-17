<?php

namespace App\Http\Controllers\Universidad;
use App\Http\Controllers\Controller;
use App\Models\Matricula\Horario;
use App\Models\Universidad\Curso;
use App\Models\Delegados\Delegado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class CursoController extends Controller
{
    //
    public function indexPaginated()
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');
        $especialidad_id = request('especialidad_id', null);

        $cursos = Curso::with('especialidad')
            ->where('nombre', 'like', "%$search%")
            ->where('cod_curso', 'like', "%$search%")
            ->when($especialidad_id, function ($query, $especialidad_id) {
                return $query->where('especialidad_id', $especialidad_id);
            })
            ->paginate($perPage);

        return response()->json(['cursos' => $cursos], 200);
    }

    public function index()
    {
        $search = request('search', '');
        $especialidad_id = request('especialidad_id', null);
        $cursos = Curso::with('especialidad')
            ->where('nombre', 'like', "%$search%")
            ->where('cod_curso', 'like', "%$search%")
            ->when($especialidad_id, function ($query, $especialidad_id) {
                return $query->where('especialidad_id', $especialidad_id);
            })
            ->get();
        return response()->json($cursos, 200);
    }

    public function getByCodigo($cod_curso)
    {
        $curso = Curso::with('especialidad')->where('cod_curso', $cod_curso)->first();
        if ($curso) {
            return response()->json($curso, 200);
        } else {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
    }

    public function show($entity_id)
    {
        try {
            $curso = Curso::with('especialidad', 'planesEstudio')->findOrFail($entity_id);
            return response()->json($curso, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'cod_curso' => 'required|string|max:6|unique:cursos,cod_curso',
            'nombre' => 'required|string|max:255',
            'creditos' => 'required|numeric|min:0',
            'estado' => 'nullable|string|in:activo,inactivo',
        ]);

        $curso = new Curso();
        $curso->especialidad_id = $validatedData['especialidad_id'];
        $curso->cod_curso = $validatedData['cod_curso'];
        $curso->nombre = $validatedData['nombre'];
        $curso->creditos = $validatedData['creditos'];
        $curso->estado = $validatedData['estado'] ?? 'activo';
        $curso->save();

        return response()->json($curso, 201);
    }

    public function update(Request $request, $entity_id)
    {
        $curso = Curso::find($entity_id);
        if (!$curso) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'cod_curso' => 'required|string|max:6|unique:cursos,cod_curso,' . $curso->id,
            'nombre' => 'required|string|max:255',
            'creditos' => 'required|numeric|min:0',
            'estado' => 'nullable|string|in:activo,inactivo',
        ]);

        $curso->especialidad_id = $validatedData['especialidad_id'];
        $curso->cod_curso = $validatedData['cod_curso'];
        $curso->nombre = $validatedData['nombre'];
        $curso->creditos = $validatedData['creditos'];
        if (isset($validatedData['estado'])) {
            $curso->estado = $validatedData['estado'];
        }
        $curso->save();

        return response()->json($curso, 200);
    }

    public function destroy($entity_id)
    {
        $curso = Curso::find($entity_id);
        if (!$curso) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $curso->delete();
        return response()->json(['message' => 'Curso eliminado'], 200);
    }

    public function obtenerDocentesPorCurso($cursoId)
    {
        // Obtener los horarios asociados al curso
        $horarios = Horario::where('curso_id', $cursoId)->with('docentes.usuario')->get();

        // Estructurar la respuesta con los docentes y su información
        $docentesPorHorario = $horarios->map(function ($horario) {
            return [
                'horario_id' => $horario->id,
                'horario_nombre' => $horario->nombre,
                'docentes' => $horario->docentes->map(function ($docente) {
                    $usuario = $docente->usuario;
                    return [
                        'nombre_completo' => $usuario->nombre . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
                        'docente_id' => $docente->id,
                    ];
                }),
            ];
        });

        return response()->json($docentesPorHorario);
    }

    public function obtenerHorariosPorCurso($cursoId)
    {
        // Obtener los horarios asociados al curso especificado
        $horarios = Horario::where('curso_id', $cursoId)
            ->select('id', 'nombre') // Seleccionamos solo los campos necesarios
            ->get();

        // Estructurar la respuesta para devolver los horarios
        $horariosData = $horarios->map(function ($horario) {
            return [
                'horario_id' => $horario->id,
                'horario_nombre' => $horario->nombre,
            ];
        });

        return response()->json($horariosData);
    }
    public function obtenerCursosPorDocente(Request $request)
    {
        $docenteId = $request->input('id'); // Obtener el ID del docente desde el cuerpo de la solicitud

        // Validar que el ID esté presente y sea un número
        if (!$docenteId || !is_numeric($docenteId)) {
            return response()->json(['message' => 'El ID del docente es requerido y debe ser un número.'], 400);
        }

        // Ejecutar la consulta SQL
        $cursos = DB::select("
            SELECT 
                cursos.id AS curso_id,
                cursos.nombre AS curso_nombre,
                cursos.cod_curso AS codigo_curso,
                cursos.creditos AS creditos,
                cursos.estado AS estado,
                horarios.id AS horario_id,
                horarios.nombre AS horario_nombre,
                horarios.codigo AS horario_codigo,
                horarios.vacantes AS vacantes,
                horarios.created_at AS horario_creacion,
                horarios.updated_at AS horario_actualizacion
            FROM 
                docente_horario
            JOIN 
                horarios ON docente_horario.horario_id = horarios.id
            JOIN 
                cursos ON horarios.curso_id = cursos.id
            WHERE 
                docente_horario.docente_id = ?
        ", [$docenteId]);

        // Verificar si hay resultados
        if (empty($cursos)) {
            return response()->json(['message' => 'No se encontraron cursos asignados para este docente.'], 404);
        }

        return response()->json($cursos);
    }
    public function obtenerCursoPorId(Request $request)
    {
        // Validar que el ID esté presente en la solicitud
        $request->validate([
            'id' => 'required|integer',
        ]);

        // Obtener el ID del curso
        $cursoId = $request->input('id');

        // Buscar el curso en la base de datos
        $curso = Curso::with(['horarios', 'especialidad']) // Relación con horarios y especialidad
            ->find($cursoId);

        // Si no se encuentra el curso, devolver un error
        if (!$curso) {
            return response()->json(['message' => 'Curso no encontrado.'], 404);
        }

        // Devolver los datos del curso en la respuesta
        return response()->json($curso);
    }
    public function obtenerHorariosPorDocenteYCursos(Request $request)
    {
        // Validar los parámetros requeridos
        $docenteId = $request->input('docente_id');
        $cursoId = $request->input('curso_id');

        if (!$docenteId || !$cursoId) {
            return response()->json(['message' => 'El ID del docente y del curso son requeridos.'], 400);
        }

        // Obtener los horarios del curso asignados al docente
        $horarios = Horario::whereHas('docentes', function ($query) use ($docenteId) {
                $query->where('docente_id', $docenteId);
            })
            ->where('curso_id', $cursoId)
            ->with(['delegado' => function ($query) {
                $query->select(
                    'delegados.id as delegado_id',
                    'delegados.horario_id',
                    'delegados.estudiante_id',
                    DB::raw("CONCAT(usuarios.nombre, ' ', usuarios.apellido_paterno, ' ', usuarios.apellido_materno) as delegado_nombre"),
                    'usuarios.email as delegado_email'
                )
                ->join('estudiantes', 'delegados.estudiante_id', '=', 'estudiantes.id') // Unir con estudiantes
                ->join('usuarios', 'estudiantes.usuario_id', '=', 'usuarios.id'); // Unir con usuarios
            }])
            ->get(['id', 'nombre', 'codigo', 'vacantes', 'created_at', 'updated_at']);

        // Verificar si hay resultados
        if ($horarios->isEmpty()) {
            return response()->json(['message' => 'No se encontraron horarios para este docente en este curso.'], 404);
        }

        // Formatear la respuesta para manejar el caso de delegados no asignados
        $horariosConDelegados = $horarios->map(function ($horario) {
            if (isset($horario->delegado) && $horario->delegado !== null) {
                // Retornar los datos del delegado si está asignado
                return [
                    'id' => $horario->id,
                    'nombre' => $horario->nombre,
                    'codigo' => $horario->codigo,
                    'vacantes' => $horario->vacantes,
                    'created_at' => $horario->created_at,
                    'updated_at' => $horario->updated_at,
                    'delegado' => [
                        'delegado_id' => $horario->delegado->delegado_id ?? null,
                        'estudiante_id' => $horario->delegado->estudiante_id ?? null,
                        'delegado_nombre' => $horario->delegado->delegado_nombre ?? 'No hay delegado asignado',
                        'delegado_email' => $horario->delegado->delegado_email ?? null,
                    ],
                ];
            }

            // Caso en que no haya delegado asignado
            return [
                'id' => $horario->id,
                'nombre' => $horario->nombre,
                'codigo' => $horario->codigo,
                'vacantes' => $horario->vacantes,
                'created_at' => $horario->created_at,
                'updated_at' => $horario->updated_at,
                'delegado' => [
                    'delegado_id' => null,
                    'estudiante_id' => null,
                    'delegado_nombre' => 'No hay delegado asignado',
                    'delegado_email' => null,
                ],
            ];
        });

        // Devolver la lista de horarios junto con los datos del delegado (o vacíos si no hay delegado)
        return response()->json($horariosConDelegados);
    }



    public function obtenerAlumnosPorHorario(Request $request)
    {
        $horarioId = $request->input('id_horario');

        // Validar que el ID del horario esté presente
        if (!$horarioId) {
            return response()->json(['message' => 'El ID del horario es requerido.'], 400);
        }

        // Verificar si el horario existe
        $horario = Horario::find($horarioId);
        if (!$horario) {
            return response()->json(['message' => 'El horario no existe.'], 404);
        }

        // Obtener los estudiantes asociados al horario desde la tabla estudiante_horario
        $estudiantes = DB::table('estudiante_horario')
            ->join('estudiantes', 'estudiante_horario.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios', 'estudiantes.usuario_id', '=', 'usuarios.id')
            ->where('estudiante_horario.horario_id', $horarioId)
            ->select(
                'estudiantes.id as estudiante_id',
                'estudiantes.codigoEstudiante as codigo',
                DB::raw("CONCAT(usuarios.nombre, ' ', usuarios.apellido_paterno, ' ', usuarios.apellido_materno) as nombre_completo"),
                'usuarios.email as email'
            )
            ->get();

        // Verificar si hay estudiantes inscritos en el horario
        if ($estudiantes->isEmpty()) {
            return response()->json(['message' => 'No hay estudiantes inscritos en este horario.'], 404);
        }

        return response()->json($estudiantes, 200);
    }
    public function actualizarDelegado(Request $request)
    {
        // Validar los datos de la solicitud
        $validated = $request->validate([
            'id_horario' => 'required|exists:horarios,id',
            'estudiante_id' => 'required|exists:estudiantes,id',
        ]);

        try {
            // Obtener el horario
            $horarioId = $validated['id_horario'];
            $estudianteId = $validated['estudiante_id'];

            // Buscar y actualizar el delegado del horario
            $delegado = Delegado::updateOrCreate(
                ['horario_id' => $horarioId], // Condición para encontrar el delegado existente
                ['estudiante_id' => $estudianteId] // Datos para actualizar o crear
            );

            // Respuesta de éxito
            return response()->json([
                'message' => 'Delegado actualizado correctamente.',
                'delegado' => [
                    'id' => $delegado->id,
                    'horario_id' => $delegado->horario_id,
                    'estudiante_id' => $delegado->estudiante_id,
                ]
            ], 200);
        } catch (\Exception $e) {
            // Respuesta de error
            return response()->json([
                'message' => 'Error al actualizar el delegado.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
