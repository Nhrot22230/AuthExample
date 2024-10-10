<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use Illuminate\Http\Request;

class InstitucionController extends Controller
{
    public function listConfiguraciones()
    {
        $per_page = request('per_page', 10);
        $page = request('page', 1);

        $instituciones = Institucion::paginate($per_page, ['*'], 'page', $page);
        return response()->json(['instituciones' => $instituciones], 200);
    }

    public function getLastConfiguracion()
    {
        $institucion = Institucion::latest()->first();
        $response = [
            'nombre' => $institucion->nombre,
            'direccion' => $institucion->direccion,
            'telefono' => $institucion->telefono,
            'logo' => $institucion->logo,
            'created_at' => $institucion->created_at,
        ];
        return response()->json($response);
    }

    public function setConfiguracion(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'direccion' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:255',
                'logo' => 'nullable|string|max:255',
            ]);
            $institucion = new Institucion();
            $institucion->nombre = $validatedData['nombre'];
            $institucion->direccion = $validatedData['direccion'] ?? '';
            $institucion->telefono = $validatedData['telefono'] ?? '';
            $institucion->logo = $validatedData['logo'] ?? '';
            $institucion->save();

            return response()->json($institucion, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
