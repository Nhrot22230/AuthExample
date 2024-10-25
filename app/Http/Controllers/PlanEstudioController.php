<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\PlanEstudio;
use App\Models\Requisito;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanEstudioController extends Controller
{
    //
    public function index()
    {
        $planesEstudio = PlanEstudio::with('requisitos')->get();
        return response()->json($planesEstudio);
    }

    public function indexPaginated()
    {
        $search = request('search', '');
        $per_page = request('per_page', 10);
        $especialidad_id = request('especialidad_id', null);

        $planesEstudio = PlanEstudio::with('requisitos')
            ->orWhere('cod_curso', 'like', "%$search%")
            ->orWhere('nombre', 'like', "%$search%")
            ->when($especialidad_id, function ($query, $especialidad_id) {
                return $query->where('especialidad_id', $especialidad_id);
            })
            ->paginate($per_page);

        return response()->json($planesEstudio, 200);
    }

    public function currentByEspecialidad($especialidad_id)
    {
        $planEstudio = PlanEstudio::with('requisitos', 'semestres')
            ->where('especialidad_id', $especialidad_id)
            ->where('estado', 'activo')
            ->first();

        return response()->json($planEstudio, 200);
    }

    public function show($id)
    {
        $planEstudio = PlanEstudio::with('requisitos', 'semestres')->find($id);
        if ($planEstudio) {
            return response()->json($planEstudio, 200);
        } else {
            return response()->json(['message' => 'Plan de estudio no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date',
                'estado' => 'required|in:activo,inactivo',
                'especialidad_id' => 'required|exists:especialidades,id',
                'semestres' => 'required|array',
                'semestres.*' => 'exists:semestres,id',
                'cursos' => 'required|array',
                'cursos.*' => 'exists:cursos,id',
                'cursos.*.requisitos' => 'nullable|array',
                'cursos.*.requisitos.*.nivel' => 'required|integer',
                'cursos.*.requisitos.*.curso_requisito_id' => 'nullable|exists:cursos,id',
                'cursos.*.requisitos.*.tipo' => 'required|string',
                'cursos.*.requisitos.*.notaMinima' => 'nullable|numeric',
                'cursos.*.requisitos.*.cantCreditos' => 'nullable|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('usuarios')->info('Error al validar los datos del plan de estudio', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos invalidos: ' . $e->getMessage()], 400);
        }

        DB::beginTransaction();
        try {
            $planEstudio = new PlanEstudio();
            $planEstudio->fill($request->all());
            $planEstudio->save();

            $planEstudio->semestres()->attach($request->semestres);

            foreach ($request->cursos as $curso) {
                $cursoModel = Curso::find($curso['id']);
                $planEstudio->cursos()->attach($cursoModel, ['nivel' => $curso['nivel']]);

                foreach ($curso['requisitos'] as $requisito) {
                    $requisitoModel = new Requisito();
                    $requisitoModel->fill($requisito);
                    $requisitoModel->curso_id = $cursoModel->id;
                    $requisitoModel->plan_estudio_id = $planEstudio->id;
                    $requisitoModel->save();
                }
            }

            DB::commit();
            return response()->json($planEstudio, 201);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::channel('usuarios')->info('Error al guardar el plan de estudio', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al guardar el plan de estudio'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date',
                'estado' => 'required|in:activo,inactivo',
                'especialidad_id' => 'required|exists:especialidades,id',
                'semestres' => 'required|array',
                'semestres.*' => 'exists:semestres,id',
                'cursos' => 'required|array',
                'cursos.*' => 'exists:cursos,id',
                'cursos.*.requisitos' => 'nullable|array',
                'cursos.*.requisitos.*.nivel' => 'required|integer',
                'cursos.*.requisitos.*.curso_requisito_id' => 'nullable|exists:cursos,id',
                'cursos.*.requisitos.*.tipo' => 'required|string',
                'cursos.*.requisitos.*.notaMinima' => 'nullable|numeric',
                'cursos.*.requisitos.*.cantCreditos' => 'nullable|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('usuarios')->info('Error al validar los datos del plan de estudio', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos invalidos: ' . $e->getMessage()], 400);
        }

        $planEstudio = PlanEstudio::find($id);
        if (!$planEstudio) {
            return response()->json(['message' => 'Plan de estudio no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $planEstudio->fill($request->all());
            $planEstudio->save();

            $planEstudio->semestres()->sync($request->semestres);

            $planEstudio->requisitos()->delete();

            foreach ($request->cursos as $curso) {
                $cursoModel = Curso::find($curso['id']);
                $planEstudio->cursos()->attach($cursoModel, ['nivel' => $curso['nivel']]);

                foreach ($curso['requisitos'] as $requisito) {
                    $requisitoModel = new Requisito();
                    $requisitoModel->fill($requisito);
                    $requisitoModel->curso_id = $cursoModel->id;
                    $requisitoModel->plan_estudio_id = $planEstudio->id;
                    $requisitoModel->save();
                }
            }
        } catch (QueryException $e) {
            DB::rollBack();
            Log::channel('usuarios')->info('Error al guardar el plan de estudio', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al guardar el plan de estudio'], 500);
        }
    }
}
