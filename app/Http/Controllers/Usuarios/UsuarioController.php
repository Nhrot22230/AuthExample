<?php

namespace App\Http\Controllers\Usuarios;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Usuarios\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');
        $tipoUsuario = request('tipo_usuario', null);

        $usuarios = Usuario::with([
            'docente',
            'estudiante',
            'administrativo',
            'roles.permissions',
        ])
            ->where(function ($query) use ($search) {
                $query->where('nombre', 'like', "%$search%")
                    ->orWhere('apellido_paterno', 'like', "%$search%")
                    ->orWhere('apellido_materno', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            })
            ->when($tipoUsuario, fn($query) => $query->whereHas($tipoUsuario))
            ->paginate($perPage)
            ->appends([
                'tipo_usuario' => $tipoUsuario,
                'search' => $search,
                'per_page' => $perPage,
            ]);
        return response()->json($usuarios, 200);
    }

    public function show($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            return response()->json($usuario, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
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
        $usuario->google_id = $validatedData['google_id'] ?? null;
        $usuario->picture = $validatedData['picture'] ?? null;
        $usuario->save();

        return response()->json(['message' => 'Usuario creado exitosamente', 'usuario' => $usuario], 201);
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        try {
            $validatedData = $request->validate([
                'nombre' => 'nullable|string|max:255',
                'apellido_paterno' => 'nullable|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255|unique:usuarios,email,' . $usuario->id,
                'password' => 'nullable|string|min:8',
                'estado' => 'nullable|string|max:50',
                'google_id' => 'nullable|string|max:255',
                'picture' => 'nullable|string|max:255',
            ]);

            if (!empty($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            } else {
                unset($validatedData['password']);
            }
            $usuario->update($validatedData);
            return response()->json(['message' => 'Usuario actualizado exitosamente', 'usuario' => $usuario], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el usuario: '. $e->getMessage()], 400);
        }
    }


    public function destroy($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
    }
    
}
