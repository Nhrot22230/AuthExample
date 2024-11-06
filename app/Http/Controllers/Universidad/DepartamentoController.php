<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Universidad\Departamento;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $departamentos = Departamento::with('facultad')
            ->where('nombre', 'like', "%$search%")->paginate($per_page);

        return response()->json($departamentos, 200);
    }

    public function indexAll()
    {
        $search = request('search', '');
        $departamentos = Departamento::with('facultad')
            ->where('nombre', 'like', "%$search%")->get();

        return response()->json($departamentos, 200);
    }

    public function show($id)
    {
        $departamento = Departamento::find($id);

        if (!$departamento) {
            return response()->json(['message' => 'Departamento no encontrado'], 404);
        }

        return response()->json($departamento, 200);
    }

    public function showByName($nombre)
    {
        $departamento = Departamento::with('facultad', 'secciones')->where('nombre', $nombre)->first();
        if (!$departamento) {
            return response()->json(['message' => 'Departamento no encontrado'], 404);
        }

        return response()->json($departamento, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'facultad_id' => 'required|exists:facultades,id',
        ]);

        $departamento = new Departamento();
        $departamento->nombre = $validatedData['nombre'];
        $departamento->descripcion = $validatedData['descripcion'];
        $departamento->facultad_id = $validatedData['facultad_id'];
        $departamento->save();

        return response()->json($departamento, 201);
    }

    public function update(Request $request, $id)
    {
        $departamento = Departamento::find($id);
        if (!$departamento) {
            return response()->json(['message' => 'Departamento no encontrado'], 404);
        }
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'facultad_id' => 'required|exists:facultades,id',
        ]);

        $departamento->nombre = $validatedData['nombre'];
        $departamento->descripcion = $validatedData['descripcion'];
        $departamento->facultad_id = $validatedData['facultad_id'];
        $departamento->save();

        return response()->json($departamento, 200);
    }

    public function destroy($id)
    {
        $departamento = Departamento::find($id);

        if (!$departamento) {
            return response()->json(['message' => 'Departamento no encontrado'], 404);
        }

        $departamento->delete();

        return response()->json($departamento, 200);
    }
}
