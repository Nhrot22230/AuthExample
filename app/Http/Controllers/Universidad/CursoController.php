<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    //
    public function index()
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');
        $especialidad_id = request('especialidad_id', null);

        $cursos = Curso::with('especialidad')
            ->where('nombre', 'like', "%$search%")
            ->when($especialidad_id, function ($query, $especialidad_id) {
                return $query->where('especialidad_id', $especialidad_id);
            })
            ->paginate($perPage);

        return response()->json(['cursos' => $cursos], 200);
    }

    public function show($id)
    {
        try {
            $curso = Curso::with('especialidad', 'planesEstudio')->findOrFail($id);
            return response()->json($curso, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'cod_curso' => 'required|string|max:6|unique:cursos,cod_curso',
            'nombre' => 'required|string|max:255',
            'creditos' => 'required|integer|min:1',
            'estado' => 'nullable|string|in:activo,inactivo',
        ]);

        $curso = new Curso();
        $curso->especialidad_id = $validatedData['especialidad_id'];
        $curso->cod_curso = $validatedData['cod_curso'];
        $curso->nombre = $validatedData['nombre'];
        $curso->creditos = $validatedData['creditos'];
        $curso->estado = $validatedData['estado'] ?? 'activo';
        $curso->save();

        return response()->json($curso, 201);
    }

    public function update(Request $request, $id)
    {
        $curso = Curso::find($id);
        if (!$curso) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'cod_curso' => 'required|string|max:6|unique:cursos,cod_curso,' . $curso->id,
            'nombre' => 'required|string|max:255',
            'creditos' => 'required|integer|min:1',
            'estado' => 'nullable|string|in:activo,inactivo',
        ]);

        $curso->especialidad_id = $validatedData['especialidad_id'];
        $curso->cod_curso = $validatedData['cod_curso'];
        $curso->nombre = $validatedData['nombre'];
        $curso->creditos = $validatedData['creditos'];
        if (isset($validatedData['estado'])) {
            $curso->estado = $validatedData['estado'];
        }
        $curso->save();

        return response()->json($curso, 200);
    }
}
