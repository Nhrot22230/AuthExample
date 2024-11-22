<?php

namespace App\Http\Controllers\Usuarios;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Usuarios\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    public function index()
    {
        $authUser = request()->get('authUser'); // Usuario autenticado
        Log::channel('debug')->info('Listando usuarios', ['auth_user' => $authUser->id]);

        try {
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

            Log::channel('debug')->info('Usuarios listados con éxito', ['auth_user' => $authUser->id]);

            return response()->json($usuarios, 200);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al listar usuarios', [
                'auth_user' => $authUser->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Error al listar usuarios'], 500);
        }
    }

    public function show($id)
    {
        $authUser = request()->get('authUser');
        Log::channel('audit-log')->info('Consulta de usuario', [
            'auth_user' => $authUser->id,
            'target_user' => $id
        ]);

        try {
            $usuario = Usuario::findOrFail($id);

            return response()->json($usuario, 200);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Usuario no encontrado', [
                'auth_user' => $authUser->id,
                'target_user' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        $authUser = request()->get('authUser');
        Log::channel('audit-log')->info('Creación de nuevo usuario iniciada', ['auth_user' => $authUser->id]);

        try {
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
            $usuario->fill($validatedData);
            $usuario->password = Hash::make($validatedData['password']);
            $usuario->estado = 'activo';
            $usuario->save();

            Log::channel('audit-log')->info('Usuario creado', [
                'auth_user' => $authUser->id,
                'new_user' => $usuario->id
            ]);

            return response()->json(['message' => 'Usuario creado exitosamente', 'usuario' => $usuario], 201);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al crear usuario', [
                'auth_user' => $authUser->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Error al crear usuario'], 400);
        }
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $authUser = request()->get('authUser');
        Log::channel('audit-log')->info('Inicio de actualización de usuario', [
            'auth_user' => $authUser->id,
            'target_user' => $id
        ]);

        $usuario = Usuario::find($id);
        if (!$usuario) {
            Log::channel('errors')->error('Usuario no encontrado', [
                'auth_user' => $authUser->id,
                'target_user' => $id
            ]);
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

            Log::channel('audit-log')->info('Usuario actualizado', [
                'auth_user' => $authUser->id,
                'updated_user' => $id
            ]);

            return response()->json(['message' => 'Usuario actualizado exitosamente', 'usuario' => $usuario], 200);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al actualizar usuario', [
                'auth_user' => $authUser->id,
                'target_user' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Error al actualizar usuario'], 400);
        }
    }

    public function destroy($id)
    {
        $authUser = request()->get('authUser');
        Log::channel('audit-log')->info('Intento de eliminación de usuario', [
            'auth_user' => $authUser->id,
            'target_user' => $id
        ]);

        $usuario = Usuario::find($id);
        if (!$usuario) {
            Log::channel('errors')->error('Usuario no encontrado para eliminación', [
                'auth_user' => $authUser->id,
                'target_user' => $id
            ]);
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        try {
            $usuario->delete();

            Log::channel('audit-log')->info('Usuario eliminado', [
                'auth_user' => $authUser->id,
                'deleted_user' => $id
            ]);

            return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al eliminar usuario', [
                'auth_user' => $authUser->id,
                'target_user' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Error al eliminar usuario'], 400);
        }
    }
}
