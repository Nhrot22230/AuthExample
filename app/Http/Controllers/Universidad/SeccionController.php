<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Seccion;
use Illuminate\Http\Request;

class SeccionController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $secciones = Seccion::where('nombre', 'like', "%$search%")->paginate($per_page);

        return response()->json($secciones, 200);
    }

    public function indexAll()
    {
        $id_departamento = request('id_departamento', null);
        if ($id_departamento) {
            $secciones = Seccion::where('departamento_id', $id_departamento)->get();
        } else {
            $secciones = Seccion::all();
        }

        return response()->json($secciones, 200);
    }

    public function show($id)
    {
        $seccion = Seccion::find($id);

        if (!$seccion) {
            return response()->json(['message' => 'Seccion no encontrada'], 404);
        }

        return response()->json($seccion, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'departamento_id' => 'required|integer|exists:departamentos,id',
        ]);

        $seccion = new Seccion();
        $seccion->nombre = $validatedData['nombre'];
        $seccion->codigoSeccion = $validatedData['codigo'];
        $seccion->descripcion = $validatedData['descripcion'];
        $seccion->departamento_id = $validatedData['departamento_id'];
        $seccion->save();

        return response()->json($seccion, 201);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'departamento_id' => 'required|integer|exists:departamentos,id',
        ]);

        $seccion = Seccion::find($id);
        if (!$seccion) {
            return response()->json(['message' => 'Seccion no encontrada'], 404);
        }
        $seccion->nombre = $validatedData['nombre'];
        $seccion->codigo = $validatedData['codigo'];
        $seccion->descripcion = $validatedData['descripcion'];
        $seccion->departamento_id = $validatedData['departamento_id'];
        $seccion->save();
        return response()->json($seccion, 200);
    }

    public function destroy($id)
    {
        $seccion = Seccion::find($id);
        if (!$seccion) {
            return response()->json(['message' => 'Seccion no encontrada'], 404);
        }
        $seccion->delete();
        return response()->json(['message' => 'Seccion eliminada'], 200);
    }
}
