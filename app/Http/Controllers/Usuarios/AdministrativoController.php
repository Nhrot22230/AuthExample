<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Administrativo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdministrativoController extends Controller
{
    /**
     * Listar todos los administrativos.
     */
    public function index()
    {
        $administrativos = Administrativo::with('usuario')->paginate(10);
        return response()->json($administrativos, 200);
    }

    /**
     * Mostrar un administrativo por su código.
     */
    public function show($codigo)
    {
        $administrativo = Administrativo::with('usuario')->where('codigoAdministrativo', $codigo)->first();

        if (!$administrativo) {
            return response()->json(['message' => 'Administrativo no encontrado'], 404);
        }

        return response()->json($administrativo, 200);
    }

    /**
     * Actualizar un administrativo por su código.
     */
    public function update(Request $request, $codigo)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8',
            'lugarTrabajo' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
        ]);

        $administrativo = Administrativo::with('usuario')->where('codigoAdministrativo', $codigo)->firstOrFail();

        $usuario = $administrativo->usuario;
        $usuario->nombre = $validatedData['nombre'];
        $usuario->apellido_paterno = $validatedData['apellido_paterno'];
        $usuario->apellido_materno = $validatedData['apellido_materno'];
        $usuario->email = $validatedData['email'];

        if (!empty($validatedData['password'])) {
            $usuario->password = Hash::make($validatedData['password']);
        }
        $usuario->save();

        $administrativo->lugarTrabajo = $validatedData['lugarTrabajo'];
        $administrativo->cargo = $validatedData['cargo'];
        $administrativo->save();

        return response()->json(['message' => 'Administrativo actualizado exitosamente', 'administrativo' => $administrativo], 200);
    }

    /**
     * Crear un nuevo administrativo.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'codigoAdministrativo' => 'required|string|max:50|unique:administrativos,codigoAdministrativo',
            'lugarTrabajo' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
        ]);

        $usuario = Usuario::firstOrCreate(
            ['email' => $validatedData['email']],
            [
                'nombre' => $validatedData['nombre'],
                'apellido_paterno' => $validatedData['apellido_paterno'],
                'apellido_materno' => $validatedData['apellido_materno'],
                'password' => Hash::make($validatedData['password']),
            ]
        );

        $administrativo = new Administrativo();
        $administrativo->usuario_id = $usuario->id;
        $administrativo->codigoAdministrativo = $validatedData['codigoAdministrativo'];
        $administrativo->lugarTrabajo = $validatedData['lugarTrabajo'];
        $administrativo->cargo = $validatedData['cargo'];

        $usuario->administrativo()->save($administrativo);
        return response()->json(['message' => 'Administrativo creado exitosamente', 'administrativo' => $administrativo], 201);
    }

    /**
     * Eliminar un administrativo por su código.
     */
    public function destroy($codigo)
    {
        $administrativo = Administrativo::where('codigoAdministrativo', $codigo)->firstOrFail();
        $administrativo->delete();
        return response()->json(['message' => 'Administrativo eliminado exitosamente'], 200);
    }
}
