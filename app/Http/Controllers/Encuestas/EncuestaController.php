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
use App\Models\Matricula\HorarioEstudianteJp;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use App\Models\Usuarios\JefePractica;
use App\Models\Encuestas\TextoRespuestaDocente;
use App\Models\Encuestas\TextoRespuestaJP;
use App\Models\Universidad\Seccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class EncuestaController extends Controller
{
    public function indexEncuesta( $seccion_id, string $tipo_encuesta): JsonResponse
    {
        if (!in_array($tipo_encuesta, ['docente', 'jefe_practica'])) {
            return response()->json(['message' => 'Tipo de encuesta no válido.'], 400);
        }
        if (!Seccion::where('id', $seccion_id)->exists()) {
            return response()->json(['message' => 'Sección no encontrada.'], 404);
        }
        $encuestas = Encuesta::where('tipo_encuesta', $tipo_encuesta)
            ->where('seccion_id', $seccion_id)
            ->select('id', 'fecha_inicio', 'fecha_fin', 'nombre_encuesta', 'disponible')
            ->get();
        if ($encuestas->isEmpty())
            return response()->json([]);

        $encuestasConTotales = $encuestas->map(function ($encuesta) use ($tipo_encuesta) {
            if ($tipo_encuesta == 'docente') {
                $totales = $this->progresoEncuestaDocenteTotales($encuesta->id);
            } else {
                $totales = $this->progresoEncuestaJPTotales($encuesta->id);
            }

            return [
                'id' => $encuesta->id,
                'nombre_encuesta' => $encuesta->nombre_encuesta,
                'fecha_inicio' => $encuesta->fecha_inicio,
                'fecha_fin' => $encuesta->fecha_fin,
                'disponible' => $encuesta->disponible,
                'total_estudiantes' => $totales['total_estudiantes'],
                'total_completaron' => $totales['total_completaron']
            ];
        });

        return response()->json($encuestasConTotales);
    }

    public function indexCursoSemestreSeccion( $seccion_id): JsonResponse
    {
        // Verificar si la sección existe
        if (!Seccion::where('id', $seccion_id)->exists()) {
            return response()->json(['message' => 'Sección no encontrada.'], 404);
        }

        // Obtener el último semestre activo
        $semestre_id = Semestre::where('estado', 'activo')->orderByDesc('fecha_inicio')->first()->id;

        Log::info('Semestre: ' . $semestre_id);

        // Obtener los cursos que tengan docentes asociados con la sección en el semestre activo
        $cursos = Curso::whereHas('docentes', function ($query) use ($seccion_id) {
            $query->where('seccion_id', $seccion_id);  // Filtrar por seccion_id
        })
        ->whereHas('horarios', function ($query) use ($semestre_id) {
            $query->where('semestre_id', $semestre_id);  // Filtrar por semestre activo
        })
        ->select('id', 'nombre', 'cod_curso')  // Seleccionar los campos que quieres devolver
        ->get();

        Log::info('Cursos: ' . $cursos->toJson());

        return response()->json($cursos);
    }


    public function countPreguntasLatestEncuesta( $seccion_id, string $tipo_encuesta): JsonResponse
    {
        if (!in_array($tipo_encuesta, ['docente', 'jefe_practica'])) {
            return response()->json(['message' => 'Tipo de encuesta no válido.'], 400);
        }

        if (!Seccion::where('id', $seccion_id)->exists()) {
            return response()->json(['message' => 'Sección no encontrada.'], 404);
        }

        // Obtener la última encuesta de acuerdo a los parámetros especificados
        $ultimaEncuesta = Encuesta::where('tipo_encuesta', $tipo_encuesta)
            ->where('seccion_id', $seccion_id)
            ->latest()
            ->first();

        // Contar las preguntas solo si existe la última encuesta, de lo contrario contar 0
        $cantidadPreguntas = $ultimaEncuesta ? $ultimaEncuesta->pregunta()->count() : 0;

        return response()->json(['cantidad_preguntas' => $cantidadPreguntas]);
    }

    public function obtenerPreguntasUltimaEncuesta( $seccion_id, string $tipo_encuesta): JsonResponse
    {
        if (!in_array($tipo_encuesta, ['docente', 'jefe_practica'])) {
            return response()->json(['message' => 'Tipo de encuesta no válido.'], 400);
        }

        if (!Seccion::where('id', $seccion_id)->exists()) {
            return response()->json(['message' => 'Sección no encontrada.'], 404);
        }

        $ultimaEncuesta = Encuesta::where('tipo_encuesta', $tipo_encuesta)
            ->where('seccion_id', $seccion_id)
            ->latest()
            ->first();

        $preguntas = $ultimaEncuesta ? $ultimaEncuesta->pregunta : [];

        return response()->json($preguntas, 200);
    }

    public function registrarNuevaEncuesta(Request $request, $seccion_id, string $tipo_encuesta): ?JsonResponse
    {
        // Validación inicial de tipo de encuesta y sección
        if (!in_array($tipo_encuesta, ['docente', 'jefe_practica'])) {
            return response()->json(['message' => 'Tipo de encuesta no válido.'], 400);
        }
        if (!Seccion::where('id', $seccion_id)->exists()) {
            return response()->json(['message' => 'Sección no encontrada.'], 404);
        }

        // Validación de los datos del request
        $validated = $request->validate([
            'nombre_encuesta' => 'required|string|max:255',
            'tipo_encuesta' => 'required|in:docente,jefe_practica',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'cursos' => 'required|array',
            'disponible' => 'required|boolean',
            'preguntas' => 'nullable|array',
        ]);

        // Obtener semestre activo
        $semestre = Semestre::where('estado', 'activo')->orderByDesc('fecha_inicio')->first();
        if (!$semestre) {
            return response()->json(['message' => 'No hay semestre activo.'], 404);
        }
        
        // Validaciones de fechas
        $fecha_inicio = Carbon::parse($validated['fecha_inicio']);
        $fecha_fin = Carbon::parse($validated['fecha_fin']);

        if ($fecha_inicio < $semestre->fecha_inicio || $fecha_inicio > $semestre->fecha_fin) {
            return response()->json(['message' => 'La fecha de inicio debe estar dentro del semestre activo.'], 400);
        }

        if ($fecha_fin < $semestre->fecha_inicio || $fecha_fin > $semestre->fecha_fin) {
            return response()->json(['message' => 'La fecha de fin debe estar dentro del semestre activo.'], 400);
        }

        DB::beginTransaction();
        try {
            // Creamos la nueva encuesta
            $encuesta = Encuesta::create([
                'nombre_encuesta' => $validated['nombre_encuesta'],
                'tipo_encuesta' => $validated['tipo_encuesta'],
                'seccion_id' => $seccion_id,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'disponible' => $validated['disponible']
            ]);
            Log::info('Encuesta creada: ' . $encuesta->toJson());

            // Asociamos los cursos con la encuesta
            $semestre_id = $semestre->id;
            Log::info('Semestre activo encontrado: ' . $semestre->toJson());

            foreach ($validated['cursos'] as $curso_id) {
                $horarios = Horario::where('curso_id', $curso_id)
                    ->where('semestre_id', $semestre_id)
                    ->get();

                foreach ($horarios as $horario) {
                    $encuesta->horario()->attach($horario->id);
                    Log::info('Horario asociado: ' . $horario->id);
                }
            }

            // Creamos las preguntas
            if (!empty($validated['preguntas'])) {
                foreach ($validated['preguntas'] as $pregunta_data) {
                    Log::info('Creando pregunta: ' . json_encode($pregunta_data));
                    $nuevaPregunta = Pregunta::create([
                        'texto_pregunta' => $pregunta_data['texto_pregunta'],
                        'tipo_respuesta' => $pregunta_data['tipo_respuesta'],
                        'tipo_pregunta' => $pregunta_data['tipo_pregunta']
                    ]);
                    $encuesta->pregunta()->attach($nuevaPregunta->id);
                    Log::info('Pregunta asociada: ' . $nuevaPregunta->id);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Encuesta creada exitosamente.',
                'encuesta_id' => $encuesta->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar encuesta: ' . $e->getMessage());
            return response()->json(['message' => 'Error al realizar el registro.'], 500);
        }
    }

    public function mostrarCursos( $encuesta_id): JsonResponse
    {
        $encuesta = Encuesta::with(['horario.curso'])->findOrFail($encuesta_id);
        if (!$encuesta) {
            return response()->json(['message' => 'Encuesta no encontrada.'], 404);
        }
        $cursosEncuesta = $encuesta->horario->map(function ($horario) {
            $curso = $horario->curso;
            return [
                'id' => $curso->id,
                'nombre' => $curso->nombre, 
                'codigo' => $curso->codigo,
            ];
        })
        ->unique('id'); 
        return response()->json($cursosEncuesta->values());
    }

    public function listarPreguntas( $encuesta_id): JsonResponse
    {
        $encuesta = Encuesta::find($encuesta_id);
        if (!$encuesta) {
            return response()->json(['message' => 'Encuesta no encontrada.'], 404);
        }

        $preguntas = $encuesta->pregunta()
            ->select('preguntas.id as id', 'preguntas.texto_pregunta', 'preguntas.tipo_respuesta', 'preguntas.tipo_pregunta')
            ->get();

        return response()->json([
            'preguntas' => $preguntas,
        ]);
    }

    public function gestionarEncuesta(Request $request, $seccion_id, $encuesta_id): JsonResponse
    {
        if (!Seccion::where('id', $seccion_id)->exists()) {
            return response()->json(['message' => 'Sección no encontrada.'], 404);
        }

        $encuesta = Encuesta::find($encuesta_id);
        if (!$encuesta) {
            return response()->json(['message' => 'Encuesta no encontrada.'], 404);
        }

        $validated = $request->validate([
            'nombre_encuesta' => 'sometimes|string|max:255',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after:fecha_inicio',
            'cursos' => 'sometimes|array',
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
                $semestreActivo = Semestre::where('estado', 'activo')->orderByDesc('fecha_inicio')->first();
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
                        $pregunta = Pregunta::find($pregunta_data['id']);
                        if ($pregunta) {
                            $pregunta->texto_pregunta = $pregunta_data['texto_pregunta'];
                            $pregunta->tipo_respuesta = $pregunta_data['tipo_respuesta'];
                            $pregunta->tipo_pregunta = $pregunta_data['tipo_pregunta'];
                            $pregunta->save();
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
                return response()->json(['message' => 'Horario no encontrado'], 404);
            }
        } elseif ($tipoEncuesta === 'jefe_practica') {
            $encuesta->load(['horario.jefePracticas.usuario']);

            $horario = $horarioId ? $encuesta->horario->firstWhere('id', $horarioId) : null;

            if ($horario) {
                $jefePractica = $horario->jefePracticas->firstWhere('id', (int) $jpId);

                if ($jefePractica) {
                    $nombreResponsable = trim(implode(' ', [
                        $jefePractica->usuario->nombre ?? '',
                        $jefePractica->usuario->apellido_paterno ?? '',
                        $jefePractica->usuario->apellido_materno ?? ''
                    ]));
                } elseif ($jpId===null){

                }
                else{
                    return response()->json(['message' => 'JP no encontrado'], 404);
                }
            }else {
                return response()->json(['message' => 'Horario no encontrado'], 404);
            }
        }

        $detalleEncuesta = [
            'id' => $encuesta->id,
            'curso' => [
                'id' => $horario->curso->id,
                'nombre' => $horario->curso->nombre,
            ],
            'horario_codigo' => $horario->codigo,
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
                    'tipo_pregunta' => $pregunta->tipo_pregunta,
                ];
            })->values(),

        ];

        Log::info("Detalle encuesta: ", $detalleEncuesta);

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
                'respuestas.*.respuesta' => 'required',
                'jp_horario_id' => 'nullable|exists:jp_horario,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
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
                $jpHorarioRelacionado = DB::table('jp_horario')
                    ->where('id', $data['jp_horario_id'])
                    ->where('horario_id', $horarioId) // Asegura que el jp_horario_id corresponde al horario_id
                    ->exists();

                if (!$jpHorarioRelacionado) {
                    return response()->json(['message' => 'El jp_horario_id no está asociado con el horario especificado'], 400);
                }
                $this->registrarRespuestasJefePractica($data, $encuesta, $data['jp_horario_id']);

                $estudianteHorarioId = HorarioEstudiante::where('horario_id', $horarioId)
                ->where('estudiante_id', $data['estudiante_id'])
                ->value('id');

                DB::table('estudiante_horario_jp')
                ->where('estudiante_horario_id', $estudianteHorarioId)
                ->where('jp_horario_id', $data['jp_horario_id'])
                ->update(['encuestaJP' => true]);

                return response()->json(['message' => 'Respuestas de jefe de práctica registradas exitosamente'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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

            // Verificar si la respuesta es cuantitativa (numérica de 1 a 5)
            if (is_numeric($valorRespuesta) && $valorRespuesta >= 1 && $valorRespuesta <= 5) {
                $respuestaDocente = RespuestasPreguntaDocente::firstOrCreate(
                    [
                        'horario_id' => (int) $horarioId,
                        'encuesta_pregunta_id' => (int) $encuestaPregunta->id,
                    ],
                    ['cant1' => 0, 'cant2' => 0, 'cant3' => 0, 'cant4' => 0, 'cant5' => 0]
                );

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
            // Si no es cuantitativa, se asume que es una respuesta de texto
            elseif (is_string($valorRespuesta)) {
                TextoRespuestaDocente::create([
                    'horario_id' => $horarioId,
                    'encuesta_pregunta_id' => $encuestaPreguntaId,
                    'respuesta' => $valorRespuesta,
                ]);
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

            // Verificar si la respuesta es cuantitativa (numérica de 1 a 5)
            if (is_numeric($valorRespuesta) && $valorRespuesta >= 1 && $valorRespuesta <= 5) {
                $respuestaJefePractica = RespuestasPreguntaJP::firstOrCreate(
                    [
                        'jp_horario_id' => (int) $jpHorarioId,
                        'encuesta_pregunta_id' => (int) $encuestaPregunta->id,
                    ],
                    ['cant1' => 0, 'cant2' => 0, 'cant3' => 0, 'cant4' => 0, 'cant5' => 0]
                );

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
            // Si no es cuantitativa, se asume que es una respuesta de texto
            elseif (is_string($valorRespuesta)) {
                TextoRespuestaJP::create([
                    'jp_horario_id' => $jpHorarioId,
                    'encuesta_pregunta_id' => $encuestaPreguntaId,
                    'respuesta' => $valorRespuesta,
                ]);
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
        
        // Obtener preguntas y respuestas cuantitativas usando consultas directas, filtrando por horario
        $preguntasConRespuestas = DB::table('encuesta_pregunta')
            ->join('preguntas', 'encuesta_pregunta.pregunta_id', '=', 'preguntas.id')
            ->leftJoin('respuesta_pregunta_docente', function($join) use ($horarioId) {
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
            
        // Obtener respuestas de texto asociadas a las preguntas de tipo "texto" para este horario
        $respuestasTexto = DB::table('texto_respuesta_docente')
            ->join('preguntas', 'texto_respuesta_docente.encuesta_pregunta_id', '=', 'preguntas.id')
            ->where('texto_respuesta_docente.horario_id', $horarioId)
            ->whereIn('preguntas.tipo_respuesta', ['texto'])
            ->select('texto_respuesta_docente.encuesta_pregunta_id', 'texto_respuesta_docente.respuesta')
            ->get()
            ->groupBy('encuesta_pregunta_id');
            
        $detallesPreguntas = $preguntasConRespuestas->map(function ($pregunta) use ($respuestasTexto) {
            $detalles = [
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
        
            // Solo agregar respuestas_texto si el tipo de respuesta es "texto"

            if ($pregunta->tipo_respuesta === 'texto' && isset($respuestasTexto[$pregunta->id])) {
                $detalles['respuestas_texto'] = $respuestasTexto[$pregunta->id]->pluck('respuesta')->toArray();
            } else {
                $detalles['respuestas_texto'] = [];
            }     
            
            return $detalles;
        });
        
        // Formatear la respuesta final
        return response()->json([
            'docente' => [
                'nombre_completo' => $usuario->nombre . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
                'codigo' => $usuario->codigo,
            ],
            'detalles_preguntas' => $detallesPreguntas,
        ]);
    }

    public function obtenerResultadosDetalleJp($encuestaId, $jpHorarioId)
    {
        // Obtener la información del JP asociado directamente a través de jp_horario_id
        $jefePractica = JefePractica::with(['usuario', 'horario.curso'])->findOrFail($jpHorarioId);
        $usuario = $jefePractica->usuario;
        $horario = $jefePractica->horario;

        // Obtener preguntas y respuestas cuantitativas usando consultas directas, filtrando por jp_horario_id
        $preguntasConRespuestas = DB::table('encuesta_pregunta')
            ->join('preguntas', 'encuesta_pregunta.pregunta_id', '=', 'preguntas.id')
            ->leftJoin('respuesta_pregunta_jp', function($join) use ($jpHorarioId) {
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

        // Obtener respuestas de texto asociadas a las preguntas de tipo "texto" para este jp_horario_id
        $respuestasTexto = DB::table('texto_respuesta_jp')
            ->join('preguntas', 'texto_respuesta_jp.encuesta_pregunta_id', '=', 'preguntas.id')
            ->where('texto_respuesta_jp.jp_horario_id', $jpHorarioId)
            ->whereIn('preguntas.tipo_respuesta', ['texto'])
            ->select('texto_respuesta_jp.encuesta_pregunta_id', 'texto_respuesta_jp.respuesta')
            ->get()
            ->groupBy('encuesta_pregunta_id');

        // Estructurar las preguntas y sus respuestas en el formato deseado
        $detallesPreguntas = $preguntasConRespuestas->map(function ($pregunta) use ($respuestasTexto) {
            $detalles = [
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

            // Si la pregunta es de tipo "texto", agregar las respuestas de texto en arrays separados
            if ($pregunta->tipo_respuesta === 'texto' && isset($respuestasTexto[$pregunta->id])) {
                $detalles['respuestas_texto'] = $respuestasTexto[$pregunta->id]->pluck('respuesta')->toArray();
            } else {
                $detalles['respuestas_texto'] = [];
            }        
        
            return $detalles;
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

    public function progresoEncuestaDocenteTotales($encuestaId)
{
    try {
        $encuesta = Encuesta::with(['horario.curso', 'horario.horarioEstudiantes'])
            ->where('id', $encuestaId)
            ->where('tipo_encuesta', 'docente')
            ->where('disponible', true)
            ->first();

        if (!$encuesta) {
            return response()->json(['message' => 'Encuesta no encontrada o no disponible'], 404);
        }

        $totalEstudiantesGeneral = 0;
        $totalCompletaronGeneral = 0;

        $encuesta->horario->each(function ($horario) use (&$totalEstudiantesGeneral, &$totalCompletaronGeneral) {
            $totalEstudiantes = $horario->horarioEstudiantes->count();
            $totalEstudiantesGeneral += $totalEstudiantes;

            $numeroEstudiantesQueCompletaron = $horario->horarioEstudiantes
                ->where('encuestaDocente', true)
                ->count();
            $totalCompletaronGeneral += $numeroEstudiantesQueCompletaron;
        });

        return [
            'total_estudiantes' => $totalEstudiantesGeneral,
            'total_completaron' => $totalCompletaronGeneral
        ];
    } catch (\Exception $e) {
        Log::error('Error en progresoEncuestaDocenteTotales: ' . $e->getMessage());
        return response()->json(['message' => 'Error interno del servidor.'], 500);
    }
}

    public function progresoEncuestaJPTotales($encuestaId)
    {
        try {
            $encuesta = Encuesta::with(['horario.horarioEstudiantes.horarioEstudianteJps'])
                ->where('id', $encuestaId)
                ->where('tipo_encuesta', 'jefe_practica')
                ->where('disponible', true)
                ->first();

            if (!$encuesta) {
                return response()->json(['message' => 'Encuesta no encontrada o no disponible'], 404);
            }

            $totalEstudiantesGeneral = 0;
            $totalCompletaronGeneral = 0;

            $encuesta->horario->each(function ($horario) use (&$totalEstudiantesGeneral, &$totalCompletaronGeneral) {

                $totalEstudiantes = $horario->horarioEstudiantes->count();
                $totalEstudiantesGeneral += $totalEstudiantes;
                $numeroEstudiantesQueCompletaron = $horario->horarioEstudiantes
                    ->flatMap(function ($horarioEstudiante) {
                        return $horarioEstudiante->horarioEstudianteJps->where('encuestaJP', true);
                    })
                    ->count();

                $totalCompletaronGeneral += $numeroEstudiantesQueCompletaron;
            });

            return [
                'total_estudiantes' => $totalEstudiantesGeneral,
                'total_completaron' => $totalCompletaronGeneral,
            ];

        } catch (\Exception $e) {
            Log::error('Error en progresoEncuestaJPTotales: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor.'], 500);
        }
    }


    public function progresoEncuestaDocentePorHorario($horarioId)
    {
        try {
            $horario = Horario::with(['horarioEstudiantes.estudiante', 'horarioEstudiantes', 'docentes.usuario'])
            ->find($horarioId);

            if (!$horario) {
                return response()->json(['message' => 'Horario no encontrado'], 404);
            }

            $totalEstudiantes = $horario->horarioEstudiantes->count();
            $totalCompletaron = $horario->horarioEstudiantes
                                ->where('encuestaDocente', true)
                                ->count();

            $docentes = $horario->docentes;

            if ($docentes->isEmpty()) {
                return response()->json(['message' => 'No hay docentes asociados a este horario'], 404);
            }

            $nombreDocente = $docentes->first()->usuario->nombre . ' ' . $docentes->first()->usuario->apellido_paterno;

            return response()->json([
                'horario' => $horario->codigo,
                'responsable' => $nombreDocente,
                'total_estudiantes' => $totalEstudiantes,
                'total_completaron' => $totalCompletaron,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en obtenerProgresoEncuestaDocentePorHorario: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor.'], 500);
        }
    }

    public function progresoEncuestaJPPorHorario($jpId)
    {
        try {
            $jp = JefePractica::with([
                'usuario', 
                'horarioEstudianteJps.horarioEstudiante',
                'horarioEstudianteJps.horarioEstudiante.horario',
                'horarioEstudianteJps.horarioEstudiante.horario.curso'
            ])->findOrFail($jpId);

            $nombreResponsable = $jp->usuario->nombre . ' ' . $jp->usuario->apellido_paterno;

            $horario = $jp->horarioEstudianteJps->first()->horarioEstudiante->horario;

            $totalEstudiantes = $horario->horarioEstudiantes->count();

            $totalCompletaron = $jp->horarioEstudianteJps->filter(function ($horarioEstudianteJp) {
                return $horarioEstudianteJp->encuestaJP == 1;
            })->count();

            $resumenHorario = [
                'horario' => $horario->codigo,
                'responsable' => $nombreResponsable,
                'total_estudiantes' => $totalEstudiantes,
                'total_completaron' => $totalCompletaron
            ];

            return response()->json($resumenHorario);
        } catch (\Exception $e) {
            Log::error('Error en obtenerProgresoEncuestaJPPorHorario: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno del servidor.'], 500);
        }
    }

    public function getEstudiantesConEncuesta($encuestaId)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);
            if (!$encuesta) {
                return response()->json(['message' => 'Encuesta no encontrada.'], 404);
            }
            // Si no se encuentran horarios asociados
            if ($encuesta->horario->isEmpty()) {
                return response()->json(['message' => 'No hay horarios asociados a esta encuesta.'], 404);
            }
            /*$estudiantes = $encuesta
                ->horario()
                ->with(['horarioEstudiantes.estudiante'])
                ->get()
                ->flatMap(function ($horario) {
                    return $horario->horarioEstudiantes;
                })
                ->map(function ($horarioEstudiante) {
                    return $horarioEstudiante->estudiante->usuario_id;
                })
                ->unique()->values();*/
            
            // Recopilar los estudiantes únicos a través de los horarios
            $estudiantes = $encuesta->horario
                ->flatMap(function ($horario) {
                    return $horario->horarioEstudiantes->map(function ($horarioEstudiante) {
                        return $horarioEstudiante->estudiante->usuario_id;
                    });
                })
                ->unique()
                ->values();
                
            Log::info("Estudiantes: ", $estudiantes);

            return response()->json($estudiantes);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocurrió un error en el servidor: ' . $e->getMessage()], 500);
        }
    }

}
