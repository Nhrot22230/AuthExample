<?php

namespace App\Http\Controllers;

use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Requisito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanEstudioController extends Controller
{
    //
    public function index()
{
    $planesEstudio = PlanEstudio::with(['especialidad', 'semestres', 'cursos.requisitos'])
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

    public function indexPaginated()
    {
        $search = request('search', '');
        $per_page = request('per_page', 10);
        $especialidad_id = request('especialidad_id', null);

        $planesEstudio = PlanEstudio::with('cursos', 'semestres', 'cursos.requisitos')
            ->when($especialidad_id, function ($query, $especialidad_id) {
                return $query->where('especialidad_id', $especialidad_id);
            })
            ->where(function ($query) use ($search) {
                $query->orWhere('cod_curso', 'like', "%$search%")
                    ->orWhere('nombre', 'like', "%$search%");
            })
            ->paginate($per_page);

        return response()->json($planesEstudio, 200);
    }

    public function currentByEspecialidad($id)
    {
        $planEstudio = PlanEstudio::with('cursos', 'semestres')
            ->where('especialidad_id', $id)
            ->where('estado', 'activo')
            ->first();

        return response()->json($planEstudio, 200);
    }

    public function show($id)
    {
        $planesEstudio = PlanEstudio::with(['especialidad', 'semestres', 'cursos.requisitos'])->find($id);

        return response()->json($planesEstudio, 200);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('errors')->info('Error al validar los datos del plan de estudio', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }

        try {
            $planEstudio = PlanEstudio::create([
                'estado' => $request->estado,
                'especialidad_id' => $request->especialidad_id,
            ]);

            if ($request->has('semestres')) {
                $semestreIds = array_column($request->semestres, 'id');
                $planEstudio->semestres()->sync($semestreIds);
            }

            if ($request->has('cursos')) {
                foreach ($request->cursos as $cursoData) {
                    $planEstudio->cursos()->attach(
                        $cursoData['id'],
                        [
                            'nivel' => $cursoData['nivel'],
                            'creditosReq' => $cursoData['creditosReq'] ?? 0
                        ]
                    );

                    if (isset($cursoData['requisitos']) && is_array($cursoData['requisitos'])) {
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


    public function update(Request $request, $id)
    {
        try {
            $request->validate([
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('errors')->info('Error al validar los datos del plan de estudio', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos invalidos: ' . $e->getMessage()], 400);
        }

        $planEstudio = PlanEstudio::find($id);
        if (!$planEstudio) {
            return response()->json(['message' => 'Plan de estudio no encontrado'], 404);
        }

        try {
            $planEstudio->update([
                'estado' => $request->estado,
                'especialidad_id' => $request->especialidad_id,
            ]);

            if ($request->has('semestres')) {
                $semestreIds = array_column($request->semestres, 'id');
                $planEstudio->semestres()->sync($semestreIds);
            }

            if ($request->has('cursos')) {
                $planEstudio->cursos()->detach();
                foreach ($request->cursos as $cursoData) {
                    $planEstudio->cursos()->attach(
                        $cursoData['id'],
                        [
                            'nivel' => $cursoData['nivel'],
                            'creditosReq' => $cursoData['creditosReq'] ?? 0
                        ]
                    );

                    if (isset($cursoData['requisitos']) && is_array($cursoData['requisitos'])) {
                        foreach ($cursoData['requisitos'] as $requisitoData) {
                            Requisito::create([
                                'curso_id' => $cursoData['id'],
                                'plan_estudio_id' => $planEstudio->id,
                                'curso_requisito_id' => $requisitoData['curso_requisito_id'],
                                'tipo' => $requisitoData['tipo'],
                                'notaMinima' => $requisitoData['notaMinima']
                            ]);
                        }
                    }
                }
            }

            return response()->json(['message' => 'Plan de estudio actualizado exitosamente', 'plan_estudio' => $planEstudio], 200);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al actualizar el plan de estudio', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al actualizar el plan de estudio: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $planEstudio = PlanEstudio::find($id);
        if (!$planEstudio) {
            return response()->json(['message' => 'Plan de estudio no encontrado'], 404);
        }

        $planEstudio->delete();
        return response()->json(['message' => 'Plan de estudio eliminado correctamente'], 200);
    }
}
