<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Universidad\Facultad;
use Illuminate\Http\Request;

class FacultadController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $facultades = Facultad::where('nombre', 'like', "%$search%")->paginate($per_page);

        return response()->json($facultades, 200);
    }

    public function indexAll()
    {
        $search = request('search', '');
        $facultades = Facultad::where('nombre', 'like', "%$search%")->get();
        return response()->json($facultades, 200);
    }

    public function show($entity_id)
    {
        $facultad = Facultad::find($entity_id);

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        return response()->json($facultad, 200);
    }

    public function showByName($nombre)
    {
        $facultad = Facultad::with('especialidades')->where('nombre', $nombre)->first();

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        return response()->json($facultad, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'abreviatura' => 'required|string|max:255',
            'anexo' => 'nullable|string',
        ]);

        $facultad = new Facultad();
        $facultad->nombre = $validatedData['nombre'];
        $facultad->abreviatura = $validatedData['abreviatura'];
        $facultad->anexo = $validatedData['anexo'];
        $facultad->save();

        return response()->json($facultad, 201);
    }

    public function update(Request $request, $entity_id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'abreviatura' => 'required|string|max:255',
            'anexo' => 'nullable|string',
        ]);

        $facultad = Facultad::find($entity_id);

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        $facultad->nombre = $validatedData['nombre'];
        $facultad->abreviatura = $validatedData['abreviatura'];
        $facultad->anexo = $validatedData['anexo'];
        $facultad->save();

        return response()->json($facultad, 200);
    }

    public function destroy($entity_id)
    {
        try {
            $facultad = Facultad::find($entity_id);
            if (!$facultad) {
                return response()->json(['message' => 'Facultad no encontrada'], 404);
            }
            $facultad->delete();
            return response()->json($facultad, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'No se puede eliminar la facultad, ya que tiene especialidades o departamentos asociados'], 400);
        }
    }
}
