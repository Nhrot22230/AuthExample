<?php

namespace App\Http\Controllers;

use App\Models\Tramites\FaseAprobacion;
use App\Models\Tramites\TemaTesis;
use App\Models\Tramites\ProcesoAprobacion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TemaTesisController extends Controller
{
    /**
     * Listar todos los temas de tesis de autoría del usuario autenticado.
     */
    public function index(Request $request)
    {
        $paginated = filter_var(request('paginated', true), FILTER_VALIDATE_BOOLEAN);
        $autor = $request->authUser;
        $perPage = $request->query('per_page', 10);
        $temasTesis = TemaTesis::with(['area.especialidad', 'autores', 'asesores'])
            ->whereHas('autores', function ($query) use ($autor) {
                $query->where('usuario_id', $autor->id);
            });

        $temas = $paginated ?
            $temasTesis->paginate($perPage) :
            $temasTesis->get();

        return response()->json($temas, 200);
    }


    /**
     * Mostrar un tema de tesis específico.
     */
    public function show($id)
    {
        $tema = TemaTesis::with(['area.especialidad', 'autores', 'asesores'])->findOrFail($id);
        return response()->json($tema);
    }

    /**
     * Crear un nuevo tema de tesis.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'resumen' => 'required|string',
            'file_id' => 'nullable|integer|exists:files,id',
            'file_firmado_id' => 'nullable|integer|exists:files,id',
            'area_id' => 'required|integer|exists:areas,id',
            'autores' => 'required|array',
            'autores.*' => 'integer|exists:usuarios,id',
            'asesores' => 'required|array',
            'asesores.*' => 'integer|exists:usuarios,id',
        ]);

        DB::beginTransaction();
        try {
            $tema = TemaTesis::create($validated);
            $tema->autores()->attach($validated['autores']);
            $tema->asesores()->attach($validated['asesores']);
            $proceso = ProcesoAprobacion::create([
                'titulo' => $validated['titulo'],
                'resumen' => $validated['resumen'],
                'tema_tesis_id' => $tema->id,
                'estado_proceso' => 'pendiente',
                'total_fases' => 3,
            ]);
            for ($i = 1; $i <= $proceso->total_fases; $i++) {
                FaseAprobacion::create([
                    'proceso_aprobacion_id' => $proceso->id,
                    'fase' => $i,
                    'estado_fase' => 'pendiente',
                ]);
            }
            $tema->save();
            DB::commit();
            return response()->json(['message' => 'Tema de tesis creado con éxito.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('errors')->error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar un tema de tesis existente.
     */
    public function update(Request $request, $id)
    {
        $tema = TemaTesis::findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'nullable|string|max:255',
            'resumen' => 'nullable|string',
            'file_id' => 'nullable|integer|exists:files,id',
            'file_firmado_id' => 'nullable|integer|exists:files,id',
            'autores' => 'required|array',
            'autores.*' => 'integer|exists:usuarios,id',
            'asesores' => 'required|array',
            'asesores.*' => 'integer|exists:usuarios,id',
        ]);

        DB::beginTransaction();
        try {
            $tema->update($validated);
            $tema->autores()->sync($validated['autores']);
            $tema->asesores()->sync($validated['asesores']);
            $tema->save();
            DB::commit();
            return response()->json(['message' => 'Tema de tesis creado con éxito.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::channel('errors')->error($e->getMessage());
            return response()->json(['message' => $e->getMessage()]);
        }
    }
}
