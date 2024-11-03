<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
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
use Illuminate\Support\Facades\DB;

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

    public function obtenerPreguntasUltimaEncuesta(int $especialidad_id, string $tipo_encuesta) : JsonResponse
    {
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
            return response()->json(['message' => 'No hay encuestas disponibles.'], 404);
        }

        $preguntas = $ultimaEncuesta->pregunta;

        return response()->json($preguntas, 200);
    }

    public function registrarNuevaEncuesta(Request $request, int $especialidad_id, string $tipo_encuesta): ?JsonResponse{
        if (!in_array($tipo_encuesta, ['docente', 'jefe_practica'])) {
            return response()->json(['message' => 'Tipo de encuesta no válido.'], 400);
        }
        if (!Especialidad::where('id', $especialidad_id)->exists()) {
            return response()->json(['message' => 'Especialidad no encontrada.'], 404);
        }
        $validated = $request->validate([
            'nombre_encuesta' => 'required|string|max:255',
            'tipo_encuesta' => 'required|in:docente,jefe_practica',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'cursos' => 'required|array',
            'disponible' => 'required|boolean',
            'preguntas_modificadas' => 'nullable|array',
            'preguntas_nuevas' => 'nullable|array',
            'preguntas_eliminadas' => 'nullable|array'
        ]);
        DB::beginTransaction();
        try {
            $ultimaEncuesta = Encuesta::where('tipo_encuesta', $validated['tipo_encuesta'])
                ->where('especialidad_id', $especialidad_id)
                ->latest()
                ->first();
            if (!$ultimaEncuesta) {
                return response()->json(['message' => 'No hay encuestas disponibles.'], 404);
            }
            $encuesta = Encuesta::create([
                'nombre_encuesta' => $validated['nombre_encuesta'],
                'tipo_encuesta' => $validated['tipo_encuesta'],
                'especialidad_id' => $especialidad_id,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'disponible' => $validated['disponible']
            ]);
            $semestre_id = Semestre::where('estado', 'activo')->first()->id;
            foreach ($validated['cursos'] as $curso_id) {
                $horarios = Horario::where('curso_id', $curso_id)->where('semestre_id', $semestre_id)->get();
                foreach ($horarios as $horario) {
                    $encuesta->horario()->attach($horario->id);
                }
            }
            if (!empty($validated['preguntas_modificadas'])) {
                foreach ($validated['preguntas_modificadas'] as $pregunta_data) {
                    $nuevaPregunta = Pregunta::create([
                        'texto_pregunta' => $pregunta_data['texto_pregunta'],
                        'tipo_respuesta' => $pregunta_data['tipo_respuesta'],
                    ]);

                    $encuesta->pregunta()->attach($nuevaPregunta->id, ['es_modificacion' => true]);
                }
            }
            if (!empty($validated['preguntas_nuevas'])) {
                foreach ($validated['preguntas_nuevas'] as $pregunta_data) {
                    $nuevaPregunta = Pregunta::create([
                        'texto_pregunta' => $pregunta_data['texto_pregunta'],
                        'tipo_respuesta' => $pregunta_data['tipo_respuesta'],
                    ]);

                    $encuesta->pregunta()->attach($nuevaPregunta->id);
                }
            }
            if ($ultimaEncuesta) {
                $preguntasNoModificadas = $ultimaEncuesta->pregunta
                    ->whereNotIn('id', array_column($validated['preguntas_modificadas'] ?? [], 'id'))
                    ->whereNotIn('id', $validated['preguntas_eliminadas'] ?? []);

                foreach ($preguntasNoModificadas as $pregunta) {
                    $encuesta->pregunta()->attach($pregunta->id);
                }
            }
            DB::commit();
            return response()->json(['message' => 'Encuesta creada exitosamente.',
                'encuesta_id' => $encuesta->id,
            ], 201);
        } catch (\Exception $e){
            DB::rollBack();
            return response()->json(['error' => 'Error al realizar el registro.'], 500);
        }
    }

    public function mostrarCursos(int $encuesta_id): JsonResponse
    {
        $encuesta = Encuesta::find($encuesta_id);
        if (!$encuesta) {
            return response()->json(['message' => 'Encuesta no encontrada.'], 404);
        }

        $semestreActivo = Semestre::where('estado', 'activo')->first();
        if (!$semestreActivo) {
            return response()->json(['message' => 'No hay semestre activo.'], 404);
        }
        $horariosAsociados = $encuesta->horario()->where('semestre_id', $semestreActivo->id)->pluck('curso_id');
        $cursosNoAsociados = Curso::where('especialidad_id', $encuesta->especialidad_id)
            ->whereNotIn('id', $horariosAsociados)
            ->select('id', 'nombre')
            ->get();
        $cursosAsociados = $encuesta->horario()->where('semestre_id', $semestreActivo->id)
            ->with('curso:id,nombre')
            ->get()
            ->pluck('curso');
        return response()->json([
            'cursos_asociados' => $cursosAsociados,
            'cursos_no_asociados' => $cursosNoAsociados,
        ]);
    }

    public function listarPreguntas(int $encuesta_id): JsonResponse
    {
        $encuesta = Encuesta::find($encuesta_id);
        if (!$encuesta) {
            return response()->json(['message' => 'Encuesta no encontrada.'], 404);
        }

        $preguntas = $encuesta->pregunta()
            ->select('preguntas.id as pregunta_id', 'preguntas.texto_pregunta', 'preguntas.tipo_respuesta')
            ->get();

        return response()->json([
            'preguntas' => $preguntas,
        ]);
    }

    public function gestionarEncuesta(Request $request, int $especialidad_id, int $encuesta_id): JsonResponse
    {
        if (!Especialidad::where('id', $especialidad_id)->exists()) {
            return response()->json(['message' => 'Especialidad no encontrada.'], 404);
        }

        $encuesta = Encuesta::find($encuesta_id);
        if (!$encuesta) {
            return response()->json(['message' => 'Encuesta no encontrada.'], 404);
        }

        $validated = $request->validate([
            'nombre_encuesta' => 'sometimes|string|max:255',
            'tipo_encuesta' => 'sometimes|in:docente,jefe_practica',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after:fecha_inicio',
            'cursos' => 'sometimes|array',
            'disponible' => 'sometimes|boolean',
            'preguntas_modificadas' => 'nullable|array',
            'preguntas_nuevas' => 'nullable|array',
            'preguntas_eliminadas' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $encuesta = Encuesta::findOrFail($encuesta_id);
            $encuesta->update(array_filter($validated));
            if (!empty($validated['cursos'])) {
                $encuesta->horario()->detach();
                $semestre_id = Semestre::where('estado', 'activo')->first()->id;

                foreach ($validated['cursos'] as $curso_id) {
                    $horarios = Horario::where('curso_id', $curso_id)->where('semestre_id', $semestre_id)->get();
                    foreach ($horarios as $horario) {
                        $encuesta->horario()->attach($horario->id);
                    }
                }
            }
            if (!empty($validated['preguntas_eliminadas'])) {
                foreach ($validated['preguntas_eliminadas'] as $pregunta_id) {
                    $pivot = $encuesta->pregunta()->where('pregunta_id', $pregunta_id)->first();
                    if ($pivot && $pivot->es_modificacion) {
                        Pregunta::destroy($pregunta_id);
                    }
                    $encuesta->pregunta()->detach($pregunta_id);
                }
            }
            if (!empty($validated['preguntas_modificadas'])) {
                foreach ($validated['preguntas_modificadas'] as $pregunta_data) {
                    $pivot = $encuesta->pregunta()->where('pregunta_id', $pregunta_data['id'])->first();
                    if ($pivot) {
                        if ($pivot->es_modificacion) {
                            $pregunta = Pregunta::find($pregunta_data['id']);
                            if ($pregunta) {
                                $pregunta->texto_pregunta = $pregunta_data['texto_pregunta'];
                                $pregunta->tipo_respuesta = $pregunta_data['tipo_respuesta'];
                                $pregunta->save();
                            }
                        } else {
                            $nuevaPregunta = Pregunta::create([
                                'texto_pregunta' => $pregunta_data['texto_pregunta'],
                                'tipo_respuesta' => $pregunta_data['tipo_respuesta'],
                            ]);
                            $encuesta->pregunta()->detach($pregunta_data['id']);
                            $encuesta->pregunta()->attach($nuevaPregunta->id, ['es_modificacion' => true]);
                        }
                    }
                }
            }
            if (!empty($validated['preguntas_nuevas'])) {
                foreach ($validated['preguntas_nuevas'] as $pregunta_data) {
                    $nuevaPregunta = Pregunta::create([
                        'texto_pregunta' => $pregunta_data['texto_pregunta'],
                        'tipo_respuesta' => $pregunta_data['tipo_respuesta'],
                    ]);
                    $encuesta->pregunta()->attach($nuevaPregunta->id);
                }
            }
            DB::commit();
            return response()->json(['message' => 'Encuesta actualizada exitosamente.'], 200);
        } catch(\Exception $e){
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
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

    public function obtenerCursosEncuesta($encuestaId)
    {
        $encuesta = Encuesta::with(['horario.curso'])->findOrFail($encuestaId);
        $cursosEncuesta = $encuesta->horario->map(function ($horario) {
            $curso = $horario->curso;

            // Suponiendo que hay un campo `completada` en la tabla pivote `encuesta_horario`
            //$estadoEncuesta = $horario->pivot->completada ? 'Completada' : 'Pendiente';

            return [
                'curso_nombre' => $curso->nombre,
                'curso_id' => $curso->id,
                //'estado' => $estadoEncuesta,
            ];
        });

        return response()->json($cursosEncuesta);
    }

    public function obtenerResultadosDetalleDocente($encuestaId, $horarioId)
    {
        // Obtener la información del horario y docente
        $horario = Horario::with(['curso', 'docentes.usuario'])->findOrFail($horarioId);
        $docente = $horario->docentes->first();
        if (!$docente) {
            return response()->json(['error' => 'Docente no encontrado para el horario'], 404);
        }
        $usuario = $docente->usuario;

        // Obtener preguntas y respuestas usando consultas directas, filtrando por horario
        $preguntasConRespuestas = DB::table('encuesta_pregunta')
            ->join('preguntas', 'encuesta_pregunta.pregunta_id', '=', 'preguntas.id')
            ->leftJoin('respuesta_pregunta_docente', function($join) use ($horarioId) {
                $join->on('encuesta_pregunta.id', '=', 'respuesta_pregunta_docente.encuesta_pregunta_id')
                    ->where('respuesta_pregunta_docente.horario_id', '=', $horarioId);
            })
            ->select(
                'preguntas.id',
                'preguntas.texto_pregunta',
                'respuesta_pregunta_docente.cant1',
                'respuesta_pregunta_docente.cant2',
                'respuesta_pregunta_docente.cant3',
                'respuesta_pregunta_docente.cant4',
                'respuesta_pregunta_docente.cant5'
            )
            ->where('encuesta_pregunta.encuesta_id', $encuestaId)
            ->get();

        // Estructurar las preguntas y sus respuestas en el formato deseado
        $detallesPreguntas = $preguntasConRespuestas->map(function ($pregunta) {
            return [
                'pregunta_id' => $pregunta->id,
                'texto_pregunta' => $pregunta->texto_pregunta,
                'respuestas' => [
                    'Totalmente de acuerdo' => $pregunta->cant5 ?? 0,
                    'De acuerdo' => $pregunta->cant4 ?? 0,
                    'Ni de acuerdo ni en desacuerdo' => $pregunta->cant3 ?? 0,
                    'En desacuerdo' => $pregunta->cant2 ?? 0,
                    'Totalmente en desacuerdo' => $pregunta->cant1 ?? 0,
                ],
            ];
        });

        // Estructurar la respuesta final
        $resultadoEncuesta = [
            'docente' => [
                'nombre_completo' => $usuario->nombre . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
                'codigo' => $usuario->codigo,
            ],
            'detalles_preguntas' => $detallesPreguntas,
        ];

        return response()->json($resultadoEncuesta);
    }
}
