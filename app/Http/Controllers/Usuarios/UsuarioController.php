<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Mostrar una lista de todos los usuarios.
     */
    public function index()
    {
        // get url parameters page and per_page
        $page = request('page', 1);
        $per_page = request('per_page', 10);

        $usuarios = Usuario::paginate($per_page, ['*'], 'page', $page);
        return response()->json($usuarios, 200);
    }

    /**
     * Mostrar un usuario específico.
     */
    public function show($id)
    {
        $usuario = Usuario::findOrFail($id);

        return response()->json($usuario, 200);
    }

    /**
     * Crear un nuevo usuario.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'google_id' => 'nullable|string|max:255',
            'picture' => 'nullable|string|max:255',
        ]);

        $usuario = new Usuario();
        $usuario->nombre = $validatedData['nombre'];
        $usuario->apellido_paterno = $validatedData['apellido_paterno'];
        $usuario->apellido_materno = $validatedData['apellido_materno'];
        $usuario->email = $validatedData['email'];
        $usuario->password = Hash::make($validatedData['password']);
        $usuario->estado = $validatedData['estado'] ?? 'activo';
        $usuario->google_id = $validatedData['google_id'];
        $usuario->picture = $validatedData['picture'];
        $usuario->save();

        return response()->json(['message' => 'Usuario creado exitosamente', 'usuario' => $usuario], 201);
    }

    /**
     * Actualizar un usuario existente.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $id,
            'password' => 'nullable|string|min:8',
            'estado' => 'nullable|string|max:50',
            'google_id' => 'nullable|string|max:255',
            'picture' => 'nullable|string|max:255',
        ]);

        $usuario = Usuario::findOrFail($id);

        $usuario->nombre = $validatedData['nombre'];
        $usuario->apellido_paterno = $validatedData['apellido_paterno'];
        $usuario->apellido_materno = $validatedData['apellido_materno'];
        $usuario->email = $validatedData['email'];
        if (!empty($validatedData['password'])) {
            $usuario->password = Hash::make($validatedData['password']);
        }

        $usuario->estado = $validatedData['estado'] ?? $usuario->estado;
        $usuario->google_id = $validatedData['google_id'] ?? $usuario->google_id;
        $usuario->picture = $validatedData['picture'] ?? $usuario->picture;

        $usuario->save();
        return response()->json(['message' => 'Usuario actualizado exitosamente', 'usuario' => $usuario], 200);
    }

    /**
     * Eliminar un usuario.
     */
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
    }
}
