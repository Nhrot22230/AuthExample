<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionsController extends Controller
{
    public function indexRoles()
    {
        $perPage = request('per_page', 10);
        $roles = Role::with('permissions')
            ->withCount('users')
            ->paginate($perPage);
        return response()->json(['roles' => $roles], 200);
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
        return response()->json(['role' => $role], 200);
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name' . $role->name,
            'permissions' => 'nullable|array',
        ]);

        $role->update($validatedData);
        if (isset($validatedData['permissions'])) {
            $role->syncPermissions($validatedData['permissions']);
        }
        return response()->json(['message' => 'Rol actualizado exitosamente', 'role' => $role], 200);
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

    public function indexPermissions()
    {
        $perPage = request('per_page', 10);
        $permissions = Permission::paginate($perPage);
        return response()->json(['permissions' => $permissions], 200);
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
            'name' => 'required|string|max:255|unique:permissions,name' . $permission->name,
        ]);

        $permission->update($validatedData);
        return response()->json(['message' => 'Permiso actualizado exitosamente', 'permission' => $permission], 200);
    }

    public function syncRoles(Request $request)
    {
        $validatedData = $request->validate([
            'roles' => 'required|array',
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);

        $usuario = Usuario::find($validatedData['usuario_id']);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->syncRoles($validatedData['roles']);
        return response()->json(['message' => 'Roles asignados exitosamente', 'user' => $usuario], 200);
    }

    public function syncPermissions(Request $request)
    {
        $validatedData = $request->validate([
            'permissions' => 'required|array',
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);

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
        return response()->json(['roles' => $roles], 200);
    }

    public function listUserPermissions($id)
    {
        $usuario = Usuario::with('permissions')->find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $permissions = $usuario->permissions()->get();
        return response()->json(['permissions' => $permissions], 200);
    }
}
