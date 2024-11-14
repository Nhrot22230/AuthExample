<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Universidad\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EspecialidadController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $especialidades = Especialidad::where('nombre', 'like', "%$search%")->paginate($per_page);

        return response()->json($especialidades, 200);
    }

    public function indexAll()
    {
        $id_facultad = request('facultad_id', null);

        $especialidades = $id_facultad ?
            Especialidad::with('areas')->where('facultad_id', $id_facultad)->get()
            : Especialidad::with('areas')->get();


        return response()->json($especialidades, 200);
    }

    public function show($id)
    {
        $especialidad = Especialidad::with('areas')->find($id);

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        return response()->json($especialidad, 200);
    }

    public function showByName($nombre)
    {
        $especialidad = Especialidad::with('areas')->where('nombre', $nombre)->first();

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        return response()->json($especialidad, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'facultad_id' => 'required|integer|exists:facultades,id',
        ]);

        $especialidad = new Especialidad();
        $especialidad->nombre = $validatedData['nombre'];
        $especialidad->descripcion = $validatedData['descripcion'];
        $especialidad->facultad_id = $validatedData['facultad_id'];
        $especialidad->save();

        return response()->json($especialidad, 201);
    }

    public function update(Request $request, $id)
    {
        $especialidad = Especialidad::find($id);
        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'facultad_id' => 'required|integer|exists:facultades,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('errors')->error($e->getMessage());
            return response()->json(['message' => 'Error en los datos enviados'], 420);
        }
        $especialidad->nombre = $validatedData['nombre'];
        $especialidad->descripcion = $validatedData['descripcion'];
        $especialidad->facultad_id = $validatedData['facultad_id'];
        $especialidad->save();

        return response()->json($especialidad, 200);
    }

    public function destroy($id)
    {
        $especialidad = Especialidad::find($id);
        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        $especialidad->delete();
        return response()->json(['message' => 'Especialidad eliminada exitosamente'], 200);
    }
}
