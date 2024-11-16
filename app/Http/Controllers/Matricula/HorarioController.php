<?php

namespace App\Http\Controllers\Matricula;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Matricula\Horario;
use App\Models\Universidad\Semestre;
use App\Models\Delegados\Delegado;
use Illuminate\Support\Facades\DB;
class HorarioController extends Controller
{
    //
    public function obtenerCursosEstudiante($estudianteId)
    {
        $semestre_id = Semestre::where('estado', 'activo')->first()->id;

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
        $semestre = Semestre::where('estado', 'activo')->first();
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
}
