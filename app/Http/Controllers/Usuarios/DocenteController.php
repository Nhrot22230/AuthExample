<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DocenteController extends Controller
{
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $docentes = Docente::with(['usuario', 'seccion', 'especialidad'])
            ->where('codigoDocente', 'like', "%$search%")
            ->paginate($per_page);
        return response()->json($docentes, 200);
    }

    public function show($codigo)
    {
        $docente = Docente::with(['usuario', 'seccion', 'especialidad'])
            ->where('codigoDocente', $codigo)
            ->first();

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        return response()->json($docente, 200);
    }

    public function update(Request $request, $codigo)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|string|email:rfc,dns|max:255',
            'codigoDocente' => 'required|string|max:50|unique:docentes,codigoDocente',
            'password' => 'nullable|string|min:8',
            'tipo' => 'required|string',
            'especialidad_id' => 'required|integer|exists:especialidades,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
            'area_id' => 'nullable|integer|exists:areas,id',
        ]);

        DB::transaction(function () use ($validatedData, $codigo) {
            $docente = Docente::with('usuario')->where('codigoDocente', $codigo)->firstOrFail();
            $usuarioData = [
                'nombre' => $validatedData['nombre'],
                'apellido_paterno' => $validatedData['apellido_paterno'],
                'apellido_materno' => $validatedData['apellido_materno'],
                'email' => $validatedData['email'],
            ];
            if (!empty($validatedData['password'])) {
                $usuarioData['password'] = Hash::make($validatedData['password']);
            }
            $docente->usuario->update($usuarioData);
            $docente->update([
                'codigoDocente' => $validatedData['codigoDocente'],
                'tipo' => $validatedData['tipo'],
                'especialidad_id' => $validatedData['especialidad_id'],
                'seccion_id' => $validatedData['seccion_id'],
                'area_id' => $validatedData['area_id'] ?? null,
            ]);
        });
        return response()->json(['message' => 'Docente actualizado exitosamente'], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|string|email:rfc,dns|max:255',
            'codigoDocente' => 'required|string|max:50|unique:docentes,codigoDocente',
            'tipo' => 'required|string',
            'especialidad_id' => 'required|integer|exists:especialidades,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
            'area_id' => 'nullable|integer|exists:areas,id',
        ]);

        $usuario = Usuario::firstOrCreate(
            ['email' => $validatedData['email']],
            [
                'nombre' => $validatedData['nombre'],
                'apellido_paterno' => $validatedData['apellido_paterno'],
                'apellido_materno' => $validatedData['apellido_materno'],
                'password' => Hash::make($validatedData['codigoDocente']),
            ]
        );

        $docente = new Docente();
        $docente->usuario_id = $usuario->id;
        $docente->codigoDocente = $validatedData['codigoDocente'];
        $docente->tipo = $validatedData['tipo'];
        $docente->especialidad_id = $validatedData['especialidad_id'];
        $docente->seccion_id = $validatedData['seccion_id'];
        $docente->area_id = $validatedData['area_id'] ?? null;

        $usuario->docente()->save($docente);
        return response()->json(['message' => 'Docente creado exitosamente', 'docente' => $docente], 201);
    }

    public function destroy($codigo)
    {
        $docente = Docente::where('codigoDocente', $codigo)->first();
        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        $docente->delete();
        return response()->json(['message' => 'Docente eliminado exitosamente'], 200);
    }
}
