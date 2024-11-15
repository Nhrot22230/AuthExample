<?php

namespace App\Http\Controllers\Convocatorias;

use App\Http\Controllers\Controller;
use App\Models\Convocatorias\Convocatoria;
use Illuminate\Http\Request;

class ConvocatoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener el número de elementos por página y la búsqueda desde la solicitud
        $perPage = request('per_page', 10);
        $search = request('search', '');

        // Obtener las convocatorias con paginación y filtrado por nombre
        $convocatorias = Convocatoria::with('gruposCriterios', 'comite', 'candidatos')  // Incluye las relaciones
            ->where('nombre', 'like', "%$search%") // Filtra por nombre (si hay búsqueda)
            ->paginate($perPage); // Paginación

        return response()->json($convocatorias, 200);
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
     * Show the form for editing the specified resource.
     */
    public function edit(Convocatoria $convocatoria)
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
