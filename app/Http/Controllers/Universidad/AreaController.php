<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Universidad\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AreaController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $areas = Area::where('nombre', 'like', "%$search%")->paginate($per_page);

        return response()->json($areas, 200);
    }

    public function indexAll()
    {
        $areas = Area::all();

        return response()->json($areas, 200);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'especialidad_id' => 'required|integer|exists:especialidades,id',
            ]);

            $area = new Area();
            $area->nombre = $validatedData['nombre'];
            $area->descripcion = $validatedData['descripcion'] ?? '';
            $area->especialidad_id = $validatedData['especialidad_id'];
            $area->save();

            return response()->json($area, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($entity_id)
    {
        $area = Area::find($entity_id);

        if (!$area) {
            return response()->json(['message' => 'Area no encontrada'], 404);
        }

        return response()->json($area, 200);
    }

    public function update(Request $request, $entity_id)
    {
        $area = Area::find($entity_id);
        if (!$area) {
            return response()->json(['message' => 'Area no encontrada'], 404);
        }
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'especialidad_id' => 'required|integer|exists:especialidades,id',
        ]);

        $area->nombre = $validatedData['nombre'];
        $area->descripcion = $validatedData['descripcion'];
        $area->especialidad_id = $validatedData['especialidad_id'];
        $area->save();

        return response()->json($area, 200);
    }

    public function destroy($entity_id)
    {
        $area = Area::find($entity_id);
        if (!$area) {
            return response()->json(['message' => 'Area no encontrada'], 404);
        }

        $area->delete();
        return response()->json(['message' => 'Area eliminada exitosamente'], 200);
    }
}
