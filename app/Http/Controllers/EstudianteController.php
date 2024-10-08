<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EstudianteController extends Controller
{
    public function index()
    {
        $estudiantes = Estudiante::with(['usuario', 'especialidad.facultad'])->get();

        return response()->json($estudiantes, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'codigo' => 'required|string',
            'semestre' => 'required|integer',
        ]);

        $estudiante = Estudiante::create($request->all());

        return response()->json($estudiante, 201);
    }

    public function show($codEstudiante)
    {
        $estudiante = Estudiante::with(['usuario', 'especialidad.facultad'])->where('codigoEstudiante', $codEstudiante)->first();
        return response()->json($estudiante, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'codigo' => 'required|string',
            'semestre' => 'required|integer',
        ]);

        $estudiante = Estudiante::find($id);
        $estudiante->update($request->all());

        return response()->json($estudiante, 200);
    }

    public function destroy($id)
    {
        try {
            $estudiante = Estudiante::find($id);
            $estudiante->delete();
            Log::channel('estudiantes')->info('Estudiante eliminado', ['estudiante' => $estudiante]);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar el estudiante'], 400);
        }
    }
}
