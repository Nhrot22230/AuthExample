<?php

namespace App\Http\Controllers\Encuestas;

use App\Http\Controllers\Controller;
use App\Models\Encuestas\Encuesta;
use App\Models\Encuestas\EncuestaPregunta;
use App\Models\Encuestas\Pregunta;
use App\Models\Encuestas\RespuestasPreguntaDocente;
use App\Models\Encuestas\RespuestasPreguntaJP;
use App\Models\Matricula\Horario;
use App\Models\Matricula\HorarioEstudiante;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Semestre;
use App\Models\Usuarios\JefePractica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncuestaController extends Controller
{
    public function indexEncuesta(int $especialidad_id, string $tipo_encuesta): JsonResponse
    {
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

    public function indexCursoSemestreEspecialidad(int $especialidad_id): JsonResponse
    {
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


    public function countPreguntasLatestEncuesta(int $especialidad_id, string $tipo_encuesta): JsonResponse
    {
        if (!in_array($tipo_encuesta, ['docente', 'jefe_practica'])) {
            return response()->json(['message' => 'Tipo de encuesta no válido.'], 400);
        }

        if (!Especialidad::where('id', $especialidad_id)->exists()) {
            return response()->json(['message' => 'Especialidad no encontrada.'], 404);
        }

        // Obtener la última encuesta de acuerdo a los parámetros especificados
        $ultimaEncuesta = Encuesta::where('tipo_encuesta', $tipo_encuesta)
            ->where('especialidad_id', $especialidad_id)
            ->latest()
            ->first();

        // Contar las preguntas solo si existe la última encuesta, de lo contrario contar 0
        $cantidadPreguntas = $ultimaEncuesta ? $ultimaEncuesta->pregunta()->count() : 0;

        return response()->json(['cantidad_preguntas' => $cantidadPreguntas]);
    }

    public function obtenerPreguntasUltimaEncuesta(int $especialidad_id, string $tipo_encuesta): JsonResponse
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

        $preguntas = $ultimaEncuesta ? $ultimaEncuesta->pregunta : [];

        return response()->json($preguntas, 200);
    }

    public function registrarNuevaEncuesta(Request $request, int $especialidad_id, string $tipo_encuesta): ?JsonResponse
    {
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
            if (!empty($validated['preguntas_nuevas'])) {
                foreach ($validated['preguntas_nuevas'] as $pregunta_data) {
                    $nuevaPregunta = Pregunta::create([
                        'texto_pregunta' => $pregunta_data['texto_pregunta'],
                        'tipo_respuesta' => $pregunta_data['tipo_respuesta'],
                        'tipo_pregunta' => $pregunta_data['tipo_pregunta']
                    ]);

                    $encuesta->pregunta()->attach($nuevaPregunta->id);
                }
            }
            if ($ultimaEncuesta) {
                if (!empty($validated['preguntas_modificadas'])) {
                    foreach ($validated['preguntas_modificadas'] as $pregunta_data) {
                        $nuevaPregunta = Pregunta::create([
                            'texto_pregunta' => $pregunta_data['texto_pregunta'],
                            'tipo_respuesta' => $pregunta_data['tipo_respuesta'],
                            'tipo_pregunta' => $pregunta_data['tipo_pregunta']
                        ]);

                        $encuesta->pregunta()->attach($nuevaPregunta->id, ['es_modificacion' => true]);
                    }
                }
                $preguntasNoModificadas = $ultimaEncuesta->pregunta
                    ->whereNotIn('id', array_column($validated['preguntas_modificadas'] ?? [], 'id'))
                    ->whereNotIn('id', $validated['preguntas_eliminadas'] ?? []);

                foreach ($preguntasNoModificadas as $pregunta) {
                    $encuesta->pregunta()->attach($pregunta->id);
                }
            }
            DB::commit();
            return response()->json([
                'message' => 'Encuesta creada exitosamente.',
                'encuesta_id' => $encuesta->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al realizar el registro.'], 500);
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
        $horariosAsociados = $encuesta->horario()
            ->where('semestre_id', $semestreActivo->id)
            ->pluck('curso_id')
            ->toArray();
        $cursos = Curso::where('especialidad_id', $encuesta->especialidad_id)
            ->select('id', 'nombre')
            ->get()
            ->map(function ($curso) use ($horariosAsociados) {
                $curso->asociado = in_array($curso->id, $horariosAsociados);
                return $curso;
            });
        return response()->json([
            'cursos' => $cursos,
        ]);
    }

    public function listarPreguntas(int $encuesta_id): JsonResponse
    {
        $encuesta = Encuesta::find($encuesta_id);
        if (!$encuesta) {
            return response()->json(['message' => 'Encuesta no encontrada.'], 404);
        }

        $preguntas = $encuesta->pregunta()
            ->select('preguntas.id as pregunta_id', 'preguntas.texto_pregunta', 'preguntas.tipo_respuesta', 'preguntas.tipo_pregunta')
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
                $semestreActivo = Semestre::where('estado', 'activo')->first();
                if (!$semestreActivo) {
                    return response()->json(['message' => 'No hay semestre activo.'], 404);
                }
                foreach ($validated['cursos'] as $curso_id) {
                    $horarios = Horario::where('curso_id', $curso_id)
                        ->where('semestre_id', $semestreActivo->id)
                        ->get();
                    foreach ($horarios as $horario) {
                        $encuesta->horario()->attach($horario->id);
                    }
                }
            }
            if (!empty($validated['preguntas_eliminadas'])) {
                foreach ($validated['preguntas_eliminadas'] as $pregunta_id) {
                    $encuestaPregunta = EncuestaPregunta::where('encuesta_id', $encuesta->id)
                        ->where('pregunta_id', $pregunta_id)
                        ->first();
                    if ($encuestaPregunta) {
                        if ($encuestaPregunta->es_modificacion) {
                            Pregunta::destroy($pregunta_id);
                        }
                        $encuestaPregunta->delete();
                    }
                }
            }
            if (!empty($validated['preguntas_modificadas'])) {
                foreach ($validated['preguntas_modificadas'] as $pregunta_data) {
                    $encuestaPregunta = EncuestaPregunta::where('encuesta_id', $encuesta->id)
                        ->where('pregunta_id', $pregunta_data['id'])
                        ->first();
                    if ($encuestaPregunta) {
                        if ($encuestaPregunta->es_modificacion) {
                            // Si es una modificación, actualizamos la pregunta
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
                                'tipo_pregunta' => $pregunta_data['tipo_pregunta'],
                            ]);
                            $encuestaPregunta->delete();
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
                        'tipo_pregunta' => $pregunta_data['tipo_pregunta'],
                    ]);
                    $encuesta->pregunta()->attach($nuevaPregunta->id);
                }
            }
            DB::commit();
            return response()->json(['message' => 'Encuesta actualizada exitosamente.'], 200);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function obtenerDetalleEncuesta($encuestaId, $horarioId, $jpId = null)
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
                        $docente->usuario->apellido_paterno . " " . $docente->usuario->apellido_materno;
                }
            } else {
                return response()->json(['message' => 'Horario no encontrado'], 404);
            }
        } elseif ($tipoEncuesta === 'jefe_practica') {
            $encuesta->load(['horario.jefePracticas.usuario']);

            $horario = $horarioId ? $encuesta->horario->firstWhere('id', $horarioId) : null;

            if ($horario) {
                $jefePractica = $horario->jefePracticas->firstWhere('id', (int) $jpId);

                if ($jefePractica) {
                    $nombreResponsable = $jefePractica->usuario->nombre . " " .
                        $jefePractica->usuario->apellido_paterno . " " . $jefePractica->usuario->apellido_materno;
                } elseif ($jpId === null) {
                } else {
                    return response()->json(['error' => 'JP no encontrado'], 404);
                }
            } else {
                return response()->json(['error' => 'Horario no encontrado'], 404);
            }
        }

        $detalleEncuesta = [
            'id' => $encuesta->id,
            'curso' => [
                'id' => $horario->curso->id,
                'nombre' => $horario->curso->nombre,
            ],
            'horario_nombre' => $horario->nombre,
            'nombre_encuesta' => $encuesta->nombre_encuesta,
            'fecha_inicio' => $encuesta->fecha_inicio,
            'fecha_fin' => $encuesta->fecha_fin,
            'tipo_encuesta' => $tipoEncuesta === 'docente' ? 'Encuesta Docente' : 'Encuesta Jefe de Práctica',
            'disponible' => $encuesta->disponible,
            'nombre_responsable' => $nombreResponsable,
            'preguntas' => $encuesta->pregunta->filter(function ($pregunta) {
                return in_array($pregunta->tipo_respuesta, ['porcentaje', 'likert']);
            })->map(function ($pregunta) {
                return [
                    'id' => $pregunta->id,
                    'tipo_respuesta' => $pregunta->tipo_respuesta,
                    'texto_pregunta' => $pregunta->texto_pregunta,
                    'tipo_pregunta' => $pregunta->tipo_pregunta,
                ];
            })->values(),
        ];

        return response()->json($detalleEncuesta);
    }

    public function registrarRespuestas(Request $request, $encuestaId, $horarioId)
    {
        $encuesta = Encuesta::with('horario', 'pregunta')->findOrFail($encuestaId);

        if (!$encuesta->horario->contains('id', $horarioId)) {
            return response()->json(['message' => 'El horario no está asociado a esta encuesta'], 400);
        }

        try {
            $data = $request->validate([
                'estudiante_id' => 'required|exists:estudiantes,id',
                'respuestas' => 'required|array',
                'respuestas.*.pregunta_id' => 'required|exists:preguntas,id',
                'respuestas.*.respuesta' => 'required|integer|min:1|max:5',
                'jp_horario_id' => 'nullable|exists:jp_horario,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['messages' => $e->errors()], 422);
        }

        $estudianteMatriculado = HorarioEstudiante::where('horario_id', $horarioId)
            ->where('estudiante_id', $data['estudiante_id'])
            ->exists();

        if (!$estudianteMatriculado) {
            return response()->json(['message' => 'El estudiante no está matriculado en el horario especificado'], 400);
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
                    return response()->json(['message' => 'jp_horario_id es requerido para la encuesta de jefe de práctica'], 400);
                }

                $this->registrarRespuestasJefePractica($data, $encuesta, $data['jp_horario_id']);

                DB::table('estudiante_horario_jp')
                    ->where('estudiante_horario_id', $horarioId)
                    ->where('jp_horario_id', $data['jp_horario_id'])
                    ->update(['encuestaJP' => true]);

                return response()->json(['message' => 'Respuestas de jefe de práctica registradas exitosamente'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    protected function registrarRespuestasDocente($data, $encuesta, $horarioId)
    {
        foreach ($data['respuestas'] as $respuesta) {
            $encuestaPreguntaId = $respuesta['pregunta_id'];
            $valorRespuesta = $respuesta['respuesta'];

            $encuestaPregunta = DB::table('encuesta_pregunta')
                ->where('pregunta_id', $encuestaPreguntaId)
                ->where('encuesta_id', $encuesta->id)
                ->first();

            if (!$encuestaPregunta) {
                throw new \Exception('Pregunta_id no está asociado a la encuesta especificada');
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
            $encuestaPreguntaId = $respuesta['pregunta_id'];
            $valorRespuesta = $respuesta['respuesta'];

            $encuestaPregunta = DB::table('encuesta_pregunta')
                ->where('pregunta_id', $encuestaPreguntaId)
                ->where('encuesta_id', $encuesta->id)
                ->first();
            if (!$encuestaPregunta) {
                throw new \Exception('Pregunta_id no está asociado a la encuesta especificada');
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
            return response()->json(['message' => 'Docente no encontrado para el horario'], 404);
        }
        $usuario = $docente->usuario;

        // Obtener preguntas y respuestas usando consultas directas, filtrando por horario
        $preguntasConRespuestas = DB::table('encuesta_pregunta')
            ->join('preguntas', 'encuesta_pregunta.pregunta_id', '=', 'preguntas.id')
            ->leftJoin('respuesta_pregunta_docente', function ($join) use ($horarioId) {
                $join->on('encuesta_pregunta.id', '=', 'respuesta_pregunta_docente.encuesta_pregunta_id')
                    ->where('respuesta_pregunta_docente.horario_id', '=', $horarioId);
            })
            ->select(
                'preguntas.id',
                'preguntas.texto_pregunta',
                'preguntas.tipo_respuesta',
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
                'tipo_respuesta' =>$pregunta->tipo_respuesta,
                'respuestas' => [
                    'cant5' => $pregunta->cant5 ?? 0,
                    'cant4' => $pregunta->cant4 ?? 0,
                    'cant3' => $pregunta->cant3 ?? 0,
                    'cant2' => $pregunta->cant2 ?? 0,
                    'cant1' => $pregunta->cant1 ?? 0,
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

    public function obtenerResultadosDetalleJp($encuestaId, $jpHorarioId)
    {
        // Obtener la información del JP asociado directamente a través de jp_horario_id
        $jefePractica = JefePractica::with(['usuario', 'horario.curso'])->findOrFail($jpHorarioId);
        $usuario = $jefePractica->usuario;
        $horario = $jefePractica->horario;

        // Obtener preguntas y respuestas usando consultas directas, filtrando por jp_horario_id
        $preguntasConRespuestas = DB::table('encuesta_pregunta')
            ->join('preguntas', 'encuesta_pregunta.pregunta_id', '=', 'preguntas.id')
            ->leftJoin('respuesta_pregunta_jp', function ($join) use ($jpHorarioId) {
                $join->on('encuesta_pregunta.id', '=', 'respuesta_pregunta_jp.encuesta_pregunta_id')
                    ->where('respuesta_pregunta_jp.jp_horario_id', '=', $jpHorarioId);
            })
            ->select(
                'preguntas.id',
                'preguntas.texto_pregunta',
                'preguntas.tipo_respuesta',
                'respuesta_pregunta_jp.cant1',
                'respuesta_pregunta_jp.cant2',
                'respuesta_pregunta_jp.cant3',
                'respuesta_pregunta_jp.cant4',
                'respuesta_pregunta_jp.cant5'
            )
            ->where('encuesta_pregunta.encuesta_id', $encuestaId)
            ->get();

        // Estructurar las preguntas y sus respuestas en el formato deseado
        $detallesPreguntas = $preguntasConRespuestas->map(function ($pregunta) {
            return [
                'pregunta_id' => $pregunta->id,
                'texto_pregunta' => $pregunta->texto_pregunta,
                'tipo_respuesta' => $pregunta->tipo_respuesta,
                'respuestas' => [
                    'cant5' => $pregunta->cant5 ?? 0,
                    'cant4' => $pregunta->cant4 ?? 0,
                    'cant3' => $pregunta->cant3 ?? 0,
                    'cant2' => $pregunta->cant2 ?? 0,
                    'cant1' => $pregunta->cant1 ?? 0,
                ],
            ];
        });

        // Estructurar la respuesta final
        $resultadoEncuesta = [
            'jefe_practica' => [
                'nombre_completo' => $usuario->nombre . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
                'codigo' => $usuario->codigo,
            ],
            'curso' => [
                'id' => $horario->curso->id,
                'nombre' => $horario->curso->nombre,
            ],
            'detalles_preguntas' => $detallesPreguntas,
        ];

        return response()->json($resultadoEncuesta);
    }
}
