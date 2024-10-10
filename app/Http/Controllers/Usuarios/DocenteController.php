<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DocenteController extends Controller
{
    public function index()
    {
        // get url parameters page and per_page
        $page = request('page', 1);
        $per_page = request('per_page', 10);

        $docentes = Docente::with(['usuario', 'seccion'])->paginate($per_page, ['*'], 'page', $page);
        return response()->json($docentes, 200);
    }

    public function show($codigo)
    {
        $docente = Docente::with(['usuario', 'seccion'])->where('codigoDocente', $codigo)->first();

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
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8',
            'tipo' => 'required|string',
            'especialidad_id' => 'required|integer',
            'seccion_id' => 'required|integer',
            'area_id' => 'required|integer',
        ]);

        $docente = Docente::with('usuario')->where('codigoDocente', $codigo)->firstOrFail();

        $usuario = $docente->usuario;
        $usuario->nombre = $validatedData['nombre'];
        $usuario->apellido_paterno = $validatedData['apellido_paterno'];
        $usuario->apellido_materno = $validatedData['apellido_materno'];
        $usuario->email = $validatedData['email'];

        if (!empty($validatedData['password'])) {
            $usuario->password = Hash::make($validatedData['password']);
        }
        $usuario->save();

        $docente->tipo = $validatedData['tipo'];
        $docente->especialidad_id = $validatedData['especialidad_id'];
        $docente->seccion_id = $validatedData['seccion_id'];
        $docente->area_id = $validatedData['area_id'];

        $docente->save();
        return response()->json(['message' => 'Docente actualizado exitosamente', 'docente' => $docente], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'codigoDocente' => 'required|string|max:50|unique:docentes,codigoDocente',
            'tipo' => 'required|string',
            'especialidad_id' => 'required|integer',
            'seccion_id' => 'required|integer',
            'area_id' => 'required|integer',
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
        $docente->area_id = $validatedData['area_id'];

        $usuario->docente()->save($docente);
        return response()->json(['message' => 'Docente creado exitosamente', 'docente' => $docente], 201);
    }

    public function destroy($codigo)
    {
        $docente = Docente::where('codigoDocente', $codigo)->firstOrFail();
        $docente->delete();
        return response()->json(['message' => 'Docente eliminado exitosamente'], 200);
    }
}
