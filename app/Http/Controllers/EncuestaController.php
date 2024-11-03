<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;


use App\Models\Curso;
use App\Models\Encuesta;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Semestre;
use App\Models\RespuestasPreguntaDocente;
use App\Models\HorarioEstudiante;
use App\Models\RespuestasPreguntaJP;

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
    
    public function obtenerDetalleEncuesta($encuestaId, $horarioId, $jpId=null)
    {
        $horario = Horario::with(['curso'])->findOrFail($horarioId);

        $encuesta = Encuesta::findOrFail($encuestaId);
        $tipoEncuesta = $encuesta->tipo_encuesta;

        $nombreResponsable = null;

        if ($tipoEncuesta === 'docente') {
            $encuesta->load(['horario.docentes.usuario']);
            
            $horario = $horarioId ? $encuesta->horario->firstWhere('id', $horarioId) : null;
    
            if ($horario) {
                $docente = $horario->docentes->first();
                if ($docente && $docente->usuario) {
                    $nombreResponsable = $docente->usuario->nombre . " " .
                    $docente->usuario->apellido_paterno . " " .$docente->usuario->apellido_materno;
                }
            } else {
                return response()->json(['error' => 'Horario no encontrado'], 404);
            }
        } elseif ($tipoEncuesta === 'jefe_practica') {
            $encuesta->load(['horario.jefePracticas.usuario']);
            
            $horario = $horarioId ? $encuesta->horario->firstWhere('id', $horarioId) : null;
        
            if ($horario) {
                $jefePractica = $horario->jefePracticas->firstWhere('usuario_id', $jpId);
                if ($jefePractica) {
                    $nombreResponsable = $jefePractica->usuario->nombre." ".
                    $jefePractica->usuario->apellido_paterno." ".$jefePractica->usuario->apellido_materno;
                } elseif ($jpId===null){

                }
                else{
                    return response()->json(['error' => 'JP no encontrado'], 404);
                }
            }else {
                return response()->json(['error' => 'Horario no encontrado'], 404);
            }
        }

        $detalleEncuesta = [
            'id' => $encuesta->id,
            'curso' => [
                'id' => $horario->curso->id,
                'nombre' => $horario->curso->nombre,
            ],
            'nombre_encuesta' => $encuesta->nombre_encuesta,
            'fecha_inicio' => $encuesta->fecha_inicio,
            'fecha_fin' => $encuesta->fecha_fin,
            'tipo_encuesta' => $tipoEncuesta === 'docente' ? 'Encuesta Docente' : 'Encuesta Jefe de Práctica',
            'disponible' => $encuesta->disponible,
            'nombre_responsable' => $nombreResponsable,
            'preguntas' => $encuesta->pregunta->map(function ($pregunta) {
                return [
                    'id' => $pregunta->id,
                    'tipo_respuesta' => $pregunta->tipo_respuesta,
                    'texto_pregunta' => $pregunta->texto_pregunta,
                ];
            }),
        ];

        return response()->json($detalleEncuesta);
    }

    public function registrarRespuestas(Request $request, $encuestaId, $horarioId)
    {
        $encuesta = Encuesta::with('horario', 'pregunta')->findOrFail($encuestaId);
        
        if (!$encuesta->horario->contains('id', $horarioId)) {
            return response()->json(['error' => 'El horario no está asociado a esta encuesta'], 400);
        }
        
        try {
            $data = $request->validate([
                'estudiante_id' => 'required|exists:estudiantes,id', // ID del estudiante
                'respuestas' => 'required|array',
                'respuestas.*.pregunta_id' => 'required|exists:preguntas,id',
                'respuestas.*.respuesta' => 'required|integer|min:1|max:5', // Escala de 1 a 5
                'jp_horario_id' => 'nullable|exists:jp_horario,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $estudianteMatriculado = HorarioEstudiante::where('horario_id', $horarioId)
            ->where('estudiante_id', $data['estudiante_id'])
            ->exists();

        if (!$estudianteMatriculado) {
            return response()->json(['error' => 'El estudiante no está matriculado en el horario especificado'], 400);
        }
                
        try {
            if ($encuesta->tipo_encuesta === 'docente') {
                $this->registrarRespuestasDocente($data, $encuesta, $horarioId);
                HorarioEstudiante::where('horario_id', $horarioId)
                    ->where('estudiante_id', $data['estudiante_id'])
                    ->update(['encuestaDocente' => true]);
                return response()->json(['message' => 'Respuestas de docente registradas exitosamente'], 200);
            } elseif ($encuesta->tipo_encuesta === 'jefe_practica') {
                if (empty($data['jp_horario_id'])) {
                    return response()->json(['error' => 'jp_horario_id es requerido para la encuesta de jefe de práctica'], 400);
                }

                $this->registrarRespuestasJefePractica($data, $encuesta, $data['jp_horario_id']);

                DB::table('estudiante_horario_jp')
                ->where('estudiante_horario_id', $horarioId)
                ->where('jp_horario_id', $data['jp_horario_id'])
                ->update(['encuestaJP' => true]);

                return response()->json(['message' => 'Respuestas de jefe de práctica registradas exitosamente'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    protected function registrarRespuestasDocente($data, $encuesta, $horarioId)
    {
        foreach ($data['respuestas'] as $respuesta) {
            $preguntaId = $respuesta['pregunta_id'];
            $valorRespuesta = $respuesta['respuesta'];
            throw new \Exception('Salgo');
            $encuestaPregunta = $encuesta->pregunta()->where('pregunta_id', $preguntaId)->first();
            
            if (!$encuestaPregunta) {
                throw new \Exception('La pregunta no está asociada a esta encuesta');
            }
            $respuestaDocente = RespuestasPreguntaDocente::firstOrCreate(
                [
                    'horario_id' => (int) $horarioId,
                    'encuesta_pregunta_id' => (int) $encuestaPregunta->id,
                ],
                ['cant1' => 0, 'cant2' => 0, 'cant3' => 0, 'cant4' => 0, 'cant5' => 0]
            );
    
            if (!$respuestaDocente) {
                throw new \Exception('No se pudo crear o encontrar el registro en RespuestasPreguntaDocente');
            }

            if ($valorRespuesta === 1) {
                $respuestaDocente->increment('cant1');
            } elseif ($valorRespuesta === 2) {
                $respuestaDocente->increment('cant2');
            } elseif ($valorRespuesta === 3) {
                $respuestaDocente->increment('cant3');
            } elseif ($valorRespuesta === 4) {
                $respuestaDocente->increment('cant4');
            } elseif ($valorRespuesta === 5) {
                $respuestaDocente->increment('cant5');
            }
        }
    }
    protected function registrarRespuestasJefePractica($data, $encuesta, $jpHorarioId)
    {
        foreach ($data['respuestas'] as $respuesta) {
            $preguntaId = $respuesta['pregunta_id'];
            $valorRespuesta = $respuesta['respuesta'];

            // Verificar si la pregunta está asociada a la encuesta
            $encuestaPregunta = $encuesta->pregunta()->where('pregunta_id', $preguntaId)->first();

            if (!$encuestaPregunta) {
                throw new \Exception('La pregunta no está asociada a esta encuesta');
            }

            // Buscar o crear la respuesta específica para el JP
            $respuestaJefePractica = RespuestasPreguntaJP::firstOrCreate(
                [
                    'jp_horario_id' => (int) $jpHorarioId,
                    'encuesta_pregunta_id' => (int) $encuestaPregunta->id,
                ],
                ['cant1' => 0, 'cant2' => 0, 'cant3' => 0, 'cant4' => 0, 'cant5' => 0]
            );

            if (!$respuestaJefePractica) {
                throw new \Exception('No se pudo crear o encontrar el registro en RespuestasPreguntaJefePractica');
            }

            // Incrementar el contador correspondiente según el valor de la respuesta
            if ($valorRespuesta === 1) {
                $respuestaJefePractica->increment('cant1');
            } elseif ($valorRespuesta === 2) {
                $respuestaJefePractica->increment('cant2');
            } elseif ($valorRespuesta === 3) {
                $respuestaJefePractica->increment('cant3');
            } elseif ($valorRespuesta === 4) {
                $respuestaJefePractica->increment('cant4');
            } elseif ($valorRespuesta === 5) {
                $respuestaJefePractica->increment('cant5');
            }
        }
    }

}
