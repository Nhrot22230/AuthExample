<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Requisito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanEstudioController extends Controller
{
    public function index($entity_id)
    {
        // Cargar los planes de estudio de la especialidad
        $planesEstudio = PlanEstudio::with(['especialidad', 'semestres', 'cursos.requisitos'])
            ->where('especialidad_id', $entity_id)
            ->get()
            ->map(function ($plan) {
                // Convertir el plan de estudio a un array
                $planArray = $plan->toArray();
                
                // Filtrar los cursos y requisitos
                $planArray['cursos'] = array_map(function ($curso) use ($plan) {
                    // Mover 'nivel' y 'creditosReq' fuera del 'pivot'
                    if (isset($curso['pivot'])) {
                        $curso['nivel'] = $curso['pivot']['nivel'];
                        $curso['creditosReq'] = $curso['pivot']['creditosReq'];

                        // Eliminar el 'pivot' después de extraer sus datos
                        unset($curso['pivot']);
                    }

                    // Filtrar los requisitos de acuerdo al plan_estudio_id
                    $curso['requisitos'] = array_filter($curso['requisitos'], function ($requisito) use ($plan) {
                        return $requisito['plan_estudio_id'] === $plan['id'];
                    });

                    // Reindexar los requisitos después del filtro
                    $curso['requisitos'] = array_values($curso['requisitos']);

                    return $curso;
                }, $planArray['cursos']);

                return $planArray;
            });

        return response()->json($planesEstudio, 200);
    }



    public function indexPaginated(Request $request, $entity_id)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('per_page', 10);

        $planesEstudio = PlanEstudio::with('cursos', 'semestres', 'cursos.requisitos')
            ->where('especialidad_id', $entity_id)
            ->where(function ($query) use ($search) {
                $query->where('cod_curso', 'like', "%$search%")
                    ->orWhere('nombre', 'like', "%$search%");
            })
            ->get()
            ->map(function ($plan) {
                // Convertir el plan de estudio a un array
                $planArray = $plan->toArray();
                
                // Filtrar los cursos y requisitos
                $planArray['cursos'] = array_map(function ($curso) use ($plan) {
                    // Mover 'nivel' y 'creditosReq' fuera del 'pivot'
                    if (isset($curso['pivot'])) {
                        $curso['nivel'] = $curso['pivot']['nivel'];
                        $curso['creditosReq'] = $curso['pivot']['creditosReq'];

                        // Eliminar el 'pivot' después de extraer sus datos
                        unset($curso['pivot']);
                    }

                    // Filtrar los requisitos de acuerdo al plan_estudio_id
                    $curso['requisitos'] = array_filter($curso['requisitos'], function ($requisito) use ($plan) {
                        return $requisito['plan_estudio_id'] === $plan['id'];
                    });

                    // Reindexar los requisitos después del filtro
                    $curso['requisitos'] = array_values($curso['requisitos']);

                    return $curso;
                }, $planArray['cursos']);

                return $planArray;
            })
            ->paginate($perPage);

        return response()->json($planesEstudio, 200);
    }

    public function currentByEspecialidad($entity_id)
    {
        $planEstudio = PlanEstudio::with('cursos', 'semestres')
            ->where('especialidad_id', $entity_id)
            ->where('estado', 'activo')
            ->first();

        if (!$planEstudio) {
            return response()->json(['message' => 'No se encontró un plan de estudio activo para esta especialidad'], 404);
        }

        return response()->json($planEstudio, 200);
    }

    public function show($entity_id, $plan_id)
    {
        $planEstudio = PlanEstudio::with(['cursos', 'semestres', 'cursos.requisitos' => function ($query) use ($plan_id) {
            // Filtrar requisitos por plan_estudio_id además de curso_id
            $query->where('plan_estudio_id', $plan_id);
        }])
        ->where('especialidad_id', $entity_id)
        ->find($plan_id);

        if (!$planEstudio) {
            return response()->json(['message' => 'Plan de estudio no encontrado'], 404);
        }

        // Transformar los cursos para mover 'nivel' y 'creditosReq' fuera del 'pivot'
        $planEstudio->cursos = $planEstudio->cursos->map(function ($curso) {
            // Mover 'nivel' y 'creditosReq' fuera del 'pivot'
            if (isset($curso->pivot)) {
                $curso->nivel = $curso->pivot->nivel;
                $curso->creditosReq = $curso->pivot->creditosReq;

                // Eliminar el 'pivot' después de extraer sus datos
                unset($curso->pivot);
            }

            // Filtrar los requisitos de acuerdo al plan_estudio_id
            $curso->requisitos = $curso->requisitos->filter(function ($requisito) use ($curso) {
                return $requisito->plan_estudio_id === $curso->plan_estudio_id;
            });

            return $curso;
        });

        return response()->json($planEstudio, 200);
    }


    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'estado' => 'required|in:activo,inactivo',
                'especialidad_id' => 'required|exists:especialidades,id',
                'semestres' => 'nullable|array',
                'semestres.*.id' => 'exists:semestres,id',
                'cursos' => 'nullable|array',
                'cursos.*.id' => 'required|exists:cursos,id',
                'cursos.*.nivel' => 'required|integer|min:0',
                'cursos.*.creditosReq' => 'nullable|integer|min:0',
                'cursos.*.requisitos' => 'nullable|array',
                'cursos.*.requisitos.*.tipo' => 'required|string',
                'cursos.*.requisitos.*.curso_requisito_id' => 'nullable|exists:cursos,id',
                'cursos.*.requisitos.*.notaMinima' => 'nullable|numeric|min:0|max:20',
            ]);

            // Encontrar el mayor nivel entre los cursos
            $maxNivel = 0;
            if (isset($validatedData['cursos'])) {
                foreach ($validatedData['cursos'] as $cursoData) {
                    $maxNivel = max($maxNivel, $cursoData['nivel']);
                }
            }

            $planEstudio = PlanEstudio::create([
                'estado' => $validatedData['estado'],
                'especialidad_id' => $validatedData['especialidad_id'],
                'cantidad_semestres' => $maxNivel,
            ]);

            if (isset($validatedData['semestres'])) {
                $semestreIds = array_column($validatedData['semestres'], 'id');
                $planEstudio->semestres()->sync($semestreIds);
            }

            if (isset($validatedData['cursos'])) {
                foreach ($validatedData['cursos'] as $cursoData) {
                    $planEstudio->cursos()->attach(
                        $cursoData['id'],
                        [
                            'nivel' => $cursoData['nivel'],
                            'creditosReq' => $cursoData['creditosReq'] ?? 0,
                        ]
                    );

                    if (isset($cursoData['requisitos'])) {
                        foreach ($cursoData['requisitos'] as $requisitoData) {
                            Requisito::create([
                                'curso_id' => $cursoData['id'],
                                'plan_estudio_id' => $planEstudio->id,
                                'curso_requisito_id' => $requisitoData['curso_requisito_id'],
                                'tipo' => $requisitoData['tipo'],
                                'notaMinima' => $requisitoData['notaMinima'],
                            ]);
                        }
                    }
                }
            }

            return response()->json(['message' => 'Plan de estudio creado exitosamente', 'plan_estudio' => $planEstudio], 201);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al crear el plan de estudio', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al crear el plan de estudio: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $entity_id, $plan_id)
    {
        $planEstudio = PlanEstudio::where('especialidad_id', $entity_id)->find($plan_id);
        if (!$planEstudio) {
            return response()->json(['message' => 'Plan de estudio no encontrado'], 404);
        }

        try {
            $validatedData = $request->validate([
                'estado' => 'required|in:activo,inactivo',
                'semestres' => 'nullable|array',
                'semestres.*.id' => 'exists:semestres,id',
                'cursos' => 'nullable|array',
                'cursos.*.id' => 'required|exists:cursos,id',
                'cursos.*.nivel' => 'required|integer|min:0',
                'cursos.*.creditosReq' => 'nullable|integer|min:0',
                'cursos.*.requisitos' => 'nullable|array',
                'cursos.*.requisitos.*.tipo' => 'required|string',
                'cursos.*.requisitos.*.curso_requisito_id' => 'nullable|exists:cursos,id',
                'cursos.*.requisitos.*.notaMinima' => 'nullable|numeric|min:0|max:20',
            ]);

            // Actualizamos el estado del plan de estudio
            $planEstudio->update(['estado' => $validatedData['estado']]);

            // Actualizar los semestres si están presentes en la solicitud
            if (isset($validatedData['semestres'])) {
                $semestreIds = array_column($validatedData['semestres'], 'id');
                $planEstudio->semestres()->sync($semestreIds);
            }

            // Actualizar los cursos, eliminando los anteriores y agregando los nuevos
            if (isset($validatedData['cursos'])) {
                // Detach de todos los cursos previos
                $planEstudio->cursos()->detach();

                // Recorremos los cursos enviados y los agregamos
                foreach ($validatedData['cursos'] as $cursoData) {
                    // Asociamos el curso al plan de estudio con el nivel y los créditos requeridos
                    $planEstudio->cursos()->attach(
                        $cursoData['id'],
                        [
                            'nivel' => $cursoData['nivel'],
                            'creditosReq' => $cursoData['creditosReq'] ?? 0,
                        ]
                    );

                    // Si existen nuevos requisitos, los eliminamos y los volvemos a crear
                    if (isset($cursoData['requisitos'])) {
                        // Eliminar requisitos antiguos para este curso
                        Requisito::where('curso_id', $cursoData['id'])
                            ->where('plan_estudio_id', $planEstudio->id)
                            ->delete();

                        // Crear los nuevos requisitos
                        foreach ($cursoData['requisitos'] as $requisitoData) {
                            Requisito::create([
                                'curso_id' => $cursoData['id'],
                                'plan_estudio_id' => $planEstudio->id,
                                'curso_requisito_id' => $requisitoData['curso_requisito_id'],
                                'tipo' => $requisitoData['tipo'],
                                'notaMinima' => $requisitoData['notaMinima'],
                            ]);
                        }
                    }
                }
            }

            return response()->json(['message' => 'Plan de estudio actualizado exitosamente', 'plan_estudio' => $planEstudio], 200);

        } catch (\Exception $e) {
            Log::error('Error al actualizar el plan de estudio', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al actualizar el plan de estudio: ' . $e->getMessage()], 500);
        }
    }


    public function destroy($entity_id, $plan_id)
    {
        $planEstudio = PlanEstudio::where('especialidad_id', $entity_id)->find($plan_id);
        if (!$planEstudio) {
            return response()->json(['message' => 'Plan de estudio no encontrado'], 404);
        }

        $planEstudio->delete();
        return response()->json(['message' => 'Plan de estudio eliminado correctamente'], 200);
    }
}
