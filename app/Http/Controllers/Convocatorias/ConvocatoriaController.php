<?php

namespace App\Http\Controllers\Convocatorias;

use App\Http\Controllers\Controller;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Convocatorias\GrupoCriterios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConvocatoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');
        $seccion = request('seccion', null);
        $filters = request('filters', []);  // This will be an array of states

        $convocatorias = Convocatoria::with('gruposCriterios', 'comite', 'candidatos')
            ->where('nombre', 'like', "%$search%")
            ->when($filters, function ($query, $filters) {
                return $query->whereIn('estado', $filters);
            })
            ->where('seccion_id', 'like', "%$seccion%")
            ->paginate($perPage);

        return response()->json($convocatorias, 200);
    }

    public function indexCriterios($entity_id)
    {
        if (!is_numeric($entity_id)) {
            return response()->json(['error' => 'Invalid entity ID.'], 400);
        }

        $perPage = request()->input('per_page', 10);
        $search = request()->input('search', '');

        $grupoCriterios = GrupoCriterios::with('convocatorias')
            ->whereHas('convocatorias', function ($query) use ($entity_id) {
                $query->where('seccion_id', $entity_id);
            })
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->paginate($perPage)
            ->appends(request()->only(['search', 'per_page']));

        return response()->json($grupoCriterios, 200);
    }


    public function listar_convocatorias_todas()
    {
        try {
            $convocatorias = Convocatoria::with('gruposCriterios', 'comite', 'candidatos')->get();

            if ($convocatorias->isEmpty()) {
                return response()->json(['message' => 'No se encontraron convocatorias'], 404);
            }

            return response()->json($convocatorias, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombreConvocatoria' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'fechaInicio' => 'required|date|before_or_equal:fechaFin',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
            'miembros' => 'required|array|min:1',
            'miembros.*' => 'integer|exists:docentes,id',
            'criteriosNuevos' => 'array',
            'criteriosNuevos.*.nombre' => 'required|string|max:255',
            'criteriosNuevos.*.obligatorio' => 'required|boolean',
            'criteriosNuevos.*.descripcion' => 'nullable|string|max:1000',
            'criteriosAntiguo' => 'array',
            'criteriosAntiguo.*' => 'integer|exists:grupo_criterios,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
        ]);

        DB::beginTransaction();
        try {
            $convocatoria = Convocatoria::create([
                'nombre' => $validatedData['nombreConvocatoria'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'fechaInicio' => $validatedData['fechaInicio'],
                'fechaFin' => $validatedData['fechaFin'],
                'estado' => 'pendiente', // Estado inicial
                'seccion_id' => $validatedData['seccion_id'],
            ]);

            if (!empty($validatedData['criteriosAntiguo'])) {
                $convocatoria->gruposCriterios()->attach($validatedData['criteriosAntiguo']);
            }

            if (!empty($validatedData['criteriosNuevos'])) {
                foreach ($validatedData['criteriosNuevos'] as $criterioNuevo) {
                    $nuevoCriterio = GrupoCriterios::create($criterioNuevo);
                    $convocatoria->gruposCriterios()->attach($nuevoCriterio->id);
                }
            }

            $convocatoria->comite()->attach($validatedData['miembros']);
            DB::commit();
            return response()->json([
                'message' => 'Convocatoria creada exitosamente.',
                'convocatoria' => $convocatoria->load('gruposCriterios', 'comite'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear la convocatoria.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombreConvocatoria' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'fechaInicio' => 'required|date|before_or_equal:fechaFin',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
            'miembros' => 'required|array|min:1',
            'miembros.*' => 'integer|exists:docentes,id',
            'criteriosNuevos' => 'array',
            'criteriosNuevos.*.nombre' => 'required|string|max:255',
            'criteriosNuevos.*.obligatorio' => 'required|boolean',
            'criteriosNuevos.*.descripcion' => 'nullable|string|max:1000',
            'criteriosAntiguo' => 'array',
            'criteriosAntiguo.*' => 'integer|exists:grupo_criterios,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
        ]);

        DB::beginTransaction();
        try {
            $convocatoria = Convocatoria::findOrFail($id);

            $convocatoria->update([
                'nombre' => $validatedData['nombreConvocatoria'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'fechaInicio' => $validatedData['fechaInicio'],
                'fechaFin' => $validatedData['fechaFin'],
                'seccion_id' => $validatedData['seccion_id'],
            ]);

            $criteriosAntiguos = $validatedData['criteriosAntiguo'] ?? [];
            $criteriosNuevosIds = [];

            if (!empty($validatedData['criteriosNuevos'])) {
                foreach ($validatedData['criteriosNuevos'] as $criterioNuevo) {
                    $nuevoCriterio = GrupoCriterios::create($criterioNuevo);
                    $criteriosNuevosIds[] = $nuevoCriterio->id;
                }
            }

            $convocatoria->gruposCriterios()->sync(array_merge($criteriosAntiguos, $criteriosNuevosIds));

            $idsAsociados = GrupoCriterios::whereHas('convocatorias', function ($query) use ($convocatoria) {
                $query->where('convocatoria_id', $convocatoria->id);
            })->pluck('id')->toArray();

            GrupoCriterios::whereNotIn('id', $idsAsociados)->delete();
            $convocatoria->comite()->sync($validatedData['miembros']);
            DB::commit();
            return response()->json([
                'message' => 'Convocatoria actualizada exitosamente.',
                'convocatoria' => $convocatoria->load('gruposCriterios', 'comite'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar la convocatoria.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
