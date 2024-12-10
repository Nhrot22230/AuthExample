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
        $planesEstudio = PlanEstudio::with(['especialidad', 'semestres', 'cursos.requisitos'])
            ->where('especialidad_id', $entity_id)
            ->get()
            ->map(function ($plan) {
                $plan->cursos = $plan->cursos->map(function ($curso) {
                    $curso->nivel = $curso->pivot->nivel;
                    $curso->creditosReq = $curso->pivot->creditosReq;
                    unset($curso->pivot);
                    return $curso;
                });
                return $plan;
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
        $planEstudio = PlanEstudio::with(['semestres', 'cursos.requisitos'])
            ->where('especialidad_id', $entity_id)
            ->find($plan_id);

        if (!$planEstudio) {
            return response()->json(['message' => 'Plan de estudio no encontrado'], 404);
        }

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

            $planEstudio->update(['estado' => $validatedData['estado']]);

            if (isset($validatedData['semestres'])) {
                $semestreIds = array_column($validatedData['semestres'], 'id');
                $planEstudio->semestres()->sync($semestreIds);
            }

            if (isset($validatedData['cursos'])) {
                $planEstudio->cursos()->detach();
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
