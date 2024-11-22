<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Universidad\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    //
    public function index()
    {
        $paginated = filter_var(request('paginated', false), FILTER_VALIDATE_BOOLEAN);
        $per_page = request('per_page', 10);
        $search = request('search', null);
        $especialidad_id = request('especialidad_id', null);

        $areas = Area::query()
            ->when($search, function ($query) use ($search) {
                return $query->where('nombre', 'like', "%$search%");
            })
            ->when($especialidad_id, function ($query) use ($especialidad_id) {
                return $query->where('especialidad_id', $especialidad_id);
            });

        $result = $paginated ? $areas->paginate($per_page) : $areas->get();
        return response()->json($result, 200);
    }

    public function store(Request $request)
    {
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
