<?php

namespace App\Http\Controllers;

use App\Models\PlanEstudio;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanEstudioController extends Controller
{
    //
    public function index()
    {
        $planesEstudio = PlanEstudio::with('cursos')->get();
        return response()->json($planesEstudio);
    }

    public function indexPaginated()
    {
        $search = request('search', '');
        $per_page = request('per_page', 10);
        $especialidad_id = request('especialidad_id', null);

        $planesEstudio = PlanEstudio::with('cursos')
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
        $planEstudio = PlanEstudio::with('cursos', 'semestres')
            ->where('especialidad_id', $especialidad_id)
            ->where('estado', 'activo')
            ->first();

        return response()->json($planEstudio, 200);
    }

    public function show($id)
    {
        $planEstudio = PlanEstudio::with('cursos', 'semestres')->find($id);
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
                'codigo' => 'required',
                'especialidad_id' => 'required|exists:especialidades,id',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'estado' => 'required',
                'cursos' => 'nullable|array',
                'cursos.*.id' => 'required|exists:cursos,id',
                'cursos.*.especialidad_id' => 'required|exists:especialidades,id',
                'semestres' => 'nullable|array',
                'semestres.*.id' => 'required|exists:semestres,id',
            ]);
        } catch (\Exception $e) {
            Log::channel('plan_estudio')->error('Error al validar los datos del plan de estudio', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json(['message' => 'Error al validar los datos: ' . $e->getMessage()], 400);
        }

        DB::beginTransaction();
        try {
            $planEstudio = PlanEstudio::create($request->only([
                'codigo',
                'especialidad_id',
                'fecha_inicio',
                'fecha_fin',
                'estado'
            ]));

            if ($request->filled('cursos')) {
                $planEstudio->cursos()->attach($request->cursos);
            }

            if ($request->filled('semestres')) {
                $planEstudio->semestres()->attach($request->semestres);
            }

            DB::commit();
            return response()->json($planEstudio, 201);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        try {
            $request->validate([
                'codigo' => 'required',
                'especialidad_id' => 'required|exists:especialidades,id',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'estado' => 'required',
                'cursos' => 'nullable|array',
                'cursos.*.id' => 'required|exists:cursos,id',
                'cursos.*.especialidad_id' => 'required|exists:especialidades,id',
                'semestres' => 'nullable|array',
                'semestres.*.id' => 'required|exists:semestres,id',
            ]);
        } catch (\Exception $e) {
            Log::channel('plan_estudio')->error('Error al validar los datos del plan de estudio', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json(['message' => 'Error al validar los datos: ' . $e->getMessage()], 400);
        }

        $planEstudio = PlanEstudio::find($id);
        if (!$planEstudio) {
            return response()->json(['message' => 'Plan de estudio no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $planEstudio->update($request->only([
                'codigo',
                'especialidad_id',
                'fecha_inicio',
                'fecha_fin',
                'estado'
            ]));

            if ($request->filled('cursos')) {
                $planEstudio->cursos()->sync($request->cursos);
            }

            if ($request->filled('semestres')) {
                $planEstudio->semestres()->sync($request->semestres);
            }

            DB::commit();
            return response()->json($planEstudio, 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
