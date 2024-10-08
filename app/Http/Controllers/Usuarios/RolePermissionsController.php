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
        $page = request('page', 1);
        $perPage = request('per_page', 10);
        $roles = Role::with('permissions')
            ->withCount('users')
            ->paginate($perPage, ['*'], 'page', $page);
        return response()->json(['roles' => $roles], 200);
    }

    public function storeRole(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'required|string|max:255',
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
        try {
            $role = Role::with('permissions')->findOrFail($id);
            return response()->json(['role' => $role], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }
    }

    public function updateRole(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'guard_name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::findOrFail($id);
        $role->update($validatedData);
        if (isset($validatedData['permissions'])) {
            $role->syncPermissions($validatedData['permissions']);
        }
        return response()->json(['message' => 'Rol actualizado exitosamente', 'role' => $role], 200);
    }

    public function destroyRole($id)
    {
        try{ 
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(['message' => 'Rol eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }
    }

    public function indexPermissions()
    {
        $page = request('page', 1);
        $perPage = request('per_page', 10);
        $permissions = Permission::paginate($perPage, ['*'], 'page', $page);
        return response()->json(['permissions' => $permissions], 200);
    }

    public function showPermission($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            return response()->json(['permission' => $permission], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
        }
    }

    public function updatePermission(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'guard_name' => 'required|string|max:255',
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update($validatedData);
        return response()->json(['message' => 'Permiso actualizado exitosamente', 'permission' => $permission], 200);
    }

    public function syncRoles(Request $request)
    {
        $validatedData = $request->validate([
            'roles' => 'required|array',
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);

        $usuario = Usuario::findOrFail($validatedData['usuario_id']);
        $usuario->syncRoles($validatedData['roles']);
        return response()->json(['message' => 'Roles asignados exitosamente', 'user' => $usuario], 200);
    }

    public function syncPermissions(Request $request)
    {
        $validatedData = $request->validate([
            'permissions' => 'required|array',
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);

        $usuario = Usuario::findOrFail($validatedData['usuario_id']);
        $usuario->syncPermissions($validatedData['permissions']);
        return response()->json(['message' => 'Permisos asignados exitosamente', 'user' => $usuario], 200);
    }

    public function listUserRoles($id)
    {
        try {
            $usuario = Usuario::with('roles')->findOrFail($id);
            $roles = $usuario->roles()->get();
            return response()->json(['roles' => $roles], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
    }

    public function listUserPermissions($id)
    {
        try {
            $usuario = Usuario::with('permissions')->findOrFail($id);
            $permissions = $usuario->permissions()->get();
            return response()->json(['permissions' => $permissions], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
    }
}
