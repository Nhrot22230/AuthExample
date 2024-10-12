<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionsController extends Controller
{
    public function indexRolesPaginated()
    {
        $perPage = request('per_page', 10);
        $roles = Role::with('permissions')
            ->withCount('users')
            ->paginate($perPage);
        return response()->json($roles, 200);
    }

    public function indexRoles()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles, 200);
    }

    public function indexPermissionsPaginated()
    {
        $perPage = request('per_page', 10);
        $permissions = Permission::paginate($perPage);
        return response()->json($permissions, 200);
    }

    public function indexPermissions()
    {
        $permissions = Permission::get();
        return response()->json($permissions, 200);
    }

    public function storeRole(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create($validatedData);
        if (isset($validatedData['permissions'])) {
            $role->syncPermissions($validatedData['permissions']);
        }
        return response()->json(['message' => 'Rol creado exitosamente', 'role' => $role], 201);
    }

    public function showRole($id)
    {
        $role = Role::with('permissions')->find($id);
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }
        return response()->json($role, 200);
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
        ]);

        $role->update($validatedData);
        if (isset($validatedData['permissions'])) {
            $role->syncPermissions($validatedData['permissions']);
        }
        return response()->json(['message' => 'Rol actualizado exitosamente'], 200);
    }


    public function destroyRole($id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $role->delete();
        return response()->json(['message' => 'Rol eliminado exitosamente'], 200);
    }

    public function showPermission($id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
        }
        return response()->json(['permission' => $permission], 200);
    }

    public function updatePermission(Request $request, $id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id . ',id',
        ]);

        $permission->update($validatedData);

        return response()->json(['message' => 'Permiso actualizado exitosamente', 'permission' => $permission], 200);
    }


    public function syncRoles(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'roles' => 'nullable|array|exists:roles,name',
                'usuario_id' => 'required|integer|exists:usuarios,id',
            ]);
        } catch (\Exception $e) {
            Log::channel('usuarios')->error('Error al asignar roles a usuario', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al asignar roles a usuario' . $e->getMessage()], 420);
        }

        $usuario = Usuario::find($validatedData['usuario_id']);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->syncRoles($validatedData['roles']);
        return response()->json(['message' => 'Roles asignados exitosamente', 'user' => $usuario], 200);
    }

    public function syncPermissions(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'permissions' => 'nullable|array|exists:permissions,name',
                'usuario_id' => 'required|integer|exists:usuarios,id',
            ]);
        } catch (\Exception $e) {
            Log::channel('usuarios')->error('Error al asignar permisos a usuario', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al asignar permisos a usuario' . $e->getMessage()], 420);
        }

        $usuario = Usuario::find($validatedData['usuario_id']);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->syncPermissions($validatedData['permissions']);
        return response()->json(['message' => 'Permisos asignados exitosamente', 'user' => $usuario], 200);
    }

    public function listUserRoles($id)
    {
        $usuario = Usuario::with('roles')->find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $roles = $usuario->roles()->get();
        return response()->json($roles, 200);
    }

    public function listUserPermissions($id)
    {
        $usuario = Usuario::with('permissions')->find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $permissions = $usuario->permissions()->get();
        return response()->json($permissions, 200);
    }
}
