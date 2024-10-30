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
  
        // Mapear la información de los cursos para la respuesta
        $cursos = $horarios->map(function ($horario) use ($estudianteId) {
            // Filtrar los horarioEstudiantes para el estudiante actual y contar sus JPs evaluados
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
        $horario = Horario::with(['curso', 'jefePracticas.usuario'])->findOrFail($horarioId);

        $jps = $horario->jefePracticas->map(function ($jp) {
            return [
                'id' => $jp->id,
                'nombre' => $jp->usuario->nombre,
                'apellido' => $jp->usuario->apellido_paterno,
                'estado' => $jp->horarioEstudianteJps->isEmpty() ? 'RESPONDER' : 'COMPLETADA'
            ];
        });

        return response()->json([
            'curso' => [
                'id' => $horario->curso->id,
                'nombre' => $horario->curso->nombre,
            ],
            'jefes_practica' => $jps,
        ]);
    }
}
