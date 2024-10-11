<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Facultad;
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

    public function show($id)
    {
        $facultad = Facultad::find($id);

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
            'departamento_id' => 'required|integer|exists:departamentos,id',
        ]);

        $facultad = new Facultad();
        $facultad->nombre = $validatedData['nombre'];
        $facultad->abreviatura = $validatedData['abreviatura'];
        $facultad->anexo = $validatedData['anexo'];
        $facultad->departamento_id = $validatedData['departamento_id'];
        $facultad->save();

        return response()->json($facultad, 201);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'abreviatura' => 'required|string|max:255',
            'anexo' => 'nullable|string',
            'departamento_id' => 'required|integer|exists:departamentos,id',
        ]);

        $facultad = Facultad::find($id);

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        $facultad->nombre = $validatedData['nombre'];
        $facultad->abreviatura = $validatedData['abreviatura'];
        $facultad->anexo = $validatedData['anexo'];
        $facultad->departamento_id = $validatedData['departamento_id'];
        $facultad->save();

        return response()->json($facultad, 200);
    }
}
