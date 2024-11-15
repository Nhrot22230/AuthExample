<?php

namespace App\Http\Controllers\Convocatorias;

use App\Http\Controllers\Controller;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Convocatorias\GrupoCriterios;
use Illuminate\Http\Request;

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
            // Obtener todas las convocatorias, con sus relaciones
            $convocatorias = Convocatoria::with('gruposCriterios', 'comite', 'candidatos')->get();

            // Si no se encuentran convocatorias, retornar mensaje adecuado
            if ($convocatorias->isEmpty()) {
                return response()->json(['message' => 'No se encontraron convocatorias'], 404);
            }

            // Devolver las convocatorias en formato JSON
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Convocatoria $convocatoria)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Convocatoria $convocatoria)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Convocatoria $convocatoria)
    {
        //
    }
}
