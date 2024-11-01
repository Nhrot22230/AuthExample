<?php

namespace App\Http\Controllers;

use App\Models\Horario;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Curso;


class HorarioController extends Controller
{
    //
    public function obtenerCursosEstudiante($estudianteId)
    {
        $anioActual = 2024;
        $periodoActual = 2;

        // Obtener los horarios en los que está inscrito el estudiante en el semestre actual
        $horarios = Horario::whereHas('semestre', function ($query) use ($anioActual, $periodoActual) {
                $query->where('anho', $anioActual)
                    ->where('periodo', $periodoActual)
                    ->whereIn('estado', ['activo', 1]);
            })
            ->whereHas('estudiantes', function ($query) use ($estudianteId) {
                $query->where('estudiante_id', $estudianteId);
            })
            ->with(['curso', 'horarioEstudiantes.horarioEstudianteJps'])
            ->get();
  
        $cursos = $horarios->map(function ($horario) use ($estudianteId) {
            $jpsEvaluados = $horario->horarioEstudiantes
                ->where('estudiante_id', $estudianteId) // Filtra el estudiante específico
                ->flatMap(function ($horarioEstudiante) {
                    return $horarioEstudiante->horarioEstudianteJps->where('encuestaJP', true);
                })
                ->count();
        
            return [
                'horario_id' => $horario->id,
                'curso_id' => $horario->curso->id,
                'curso_nombre' => $horario->curso->nombre,
                'jps_evaluados' => $jpsEvaluados,
            ];
        });

        return response()->json([
            'cursos' => $cursos,
        ]);
    }
    public function obtenerJps($horarioId)
    {
        // Cargar el horario junto con las relaciones necesarias
        $horario = Horario::with([
            'curso',
            'horarioEstudiantes.horarioEstudianteJps',
            'jefePracticas.usuario'
        ])->findOrFail($horarioId);

        // Mapear la información de los jefes de práctica asociados al horario
        $jps = $horario->jefePracticas->map(function ($jp) use ($horario) {
            return [
                'id' => $jp->id,
                'nombre' => $jp->usuario->nombre,
                'apellido_paterno' => $jp->usuario->apellido_paterno,
                'apellido_materno' => $jp->usuario->apellido_materno,
                'estado' => $horario->horarioEstudiantes
                    ->flatMap(function ($horarioEstudiante) use ($jp) {
                        return $horarioEstudiante->horarioEstudianteJps
                            ->where('jp_horario_id', $jp->id)
                            ->pluck('encuestaJP');
                    })
                    ->first(), 
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
        $anioActual = 2024;
        $periodoActual = 2;

        $horarios = Horario::whereHas('semestre', function ($query) use ($anioActual, $periodoActual) {
            $query->where('anho', $anioActual)
                ->where('periodo', $periodoActual)
                ->whereIn('estado', ['activo', 1]);
        })
        ->whereHas('horarioEstudiantes', function ($query) use ($estudianteId) {
            $query->where('estudiante_id', $estudianteId);
        })
        ->with(['curso', 'horarioEstudiantes' => function ($query) use ($estudianteId) {
            $query->where('estudiante_id', $estudianteId)
                ->select('horario_id', 'estudiante_id', 'encuestaDocente');
        }])
        ->get();

    $cursos = $horarios->map(function ($horario) {
        $encuesta = optional($horario->horarioEstudiantes->first())->encuestaDocente;
        $encuestas = $horario->encuestas->map(function ($encuesta) {
            return $encuesta->id;
        });

        return [
            'horario_id' => $horario->id,
            'curso_id' => $horario->curso->id,
            'curso_nombre' => $horario->curso->nombre,
            'estado_encuesta' => $horario->horarioEstudiantes->first()->encuestaDocente,
            'encuestas' => $encuestas,
        ];
    });

    return response()->json([
        'cursos' => $cursos,
        ]);
    }
}
