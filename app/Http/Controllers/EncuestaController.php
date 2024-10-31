<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Encuesta;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Semestre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Psy\Util\Json;

class EncuestaController extends Controller
{
    public function indexEncuesta(int $especialidad_id, string $tipo_encuesta) : JsonResponse{
        if (!in_array($tipo_encuesta, ['docente', 'jefe_practica'])) {
            return response()->json(['message' => 'Tipo de encuesta no válido.'], 400);
        }

        if (!Especialidad::where('id', $especialidad_id)->exists()) {
            return response()->json(['message' => 'Especialidad no encontrada.'], 404);
        }

        $encuestas = Encuesta::where('tipo_encuesta', $tipo_encuesta)
            ->where('especialidad_id', $especialidad_id)
            ->select('id', 'fecha_inicio', 'fecha_fin', 'nombre_encuesta', 'disponible')
            ->get();

        return response()->json($encuestas);
    }

    public function indexCursoSemestreEspecialidad(int $especialidad_id): JsonResponse {
        if (!Especialidad::where('id', $especialidad_id)->exists()) {
            return response()->json(['message' => 'Especialidad no encontrada.'], 404);
        }
        echo "Hola";
        $semestre_id = Semestre::where('estado', 'activo')->first()->id;

        $cursos = Curso::where('especialidad_id', $especialidad_id)
            ->whereHas('horarios', function ($query) use ($semestre_id) {
                $query->where('semestre_id', $semestre_id);
            })
            ->select('id', 'nombre', 'cod_curso')
            ->get();

        return response()->json($cursos);
    }

    public function countPreguntasLatestEncuesta(int $especialidad_id, string $tipo_encuesta): JsonResponse {
        if (!in_array($tipo_encuesta, ['docente', 'jefe_practica'])) {
            return response()->json(['message' => 'Tipo de encuesta no válido.'], 400);
        }

        if (!Especialidad::where('id', $especialidad_id)->exists()) {
            return response()->json(['message' => 'Especialidad no encontrada.'], 404);
        }

        $ultimaEncuesta = Encuesta::where('tipo_encuesta', $tipo_encuesta)
            ->where('especialidad_id', $especialidad_id)
            ->latest()
            ->first();

        if (!$ultimaEncuesta) {
            return response()->json(['message' => 'No hay encuestas de este tipo para la especialidad especificada.'], 404);
        }

        $cantidadPreguntas = $ultimaEncuesta->pregunta()->count();

        return response()->json(['cantidad_preguntas' => $cantidadPreguntas]);
    }
}
