<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionsController extends Controller
{
    /**
     * Asignar un rol a un usuario.
     */
    public function assignRole(Request $request)
    {
        $validatedData = $request->validate([
            'role' => 'required|string|exists:roles,name',
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);

        $usuario = Usuario::findOrFail($validatedData['usuario_id']);
        $usuario->assignRole($validatedData['role']);
        return response()->json(['message' => 'Rol asignado exitosamente', 'user' => $usuario], 200);
    }

    /**
     * Quitar un rol a un usuario.
     */
    public function revokeRole(Request $request)
    {
        $validatedData = $request->validate([
            'role' => 'required|string|exists:roles,name',
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);
        $usuario = Usuario::findOrFail($validatedData['usuario_id']);
        $usuario->removeRole($validatedData['role']);
        return response()->json(['message' => 'Rol removido exitosamente', 'user' => $usuario], 200);
    }

    /**
     * Asignar un permiso a un usuario.
     */
    public function assignPermission(Request $request)
    {
        $validatedData = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);
        $usuario = Usuario::find($validatedData['usuario_id']);
        $usuario->givePermissionTo($validatedData['permission']);
        return response()->json(['message' => 'Permiso asignado exitosamente', 'user' => $usuario], 200);
    }

    /**
     * Quitar un permiso a un usuario.
     */
    public function revokePermission(Request $request)
    {
        $validatedData = $request->validate([
            'permission' => 'required|string|exists:permissions,name',
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);
        $usuario = Usuario::find($validatedData['usuario_id']);
        $usuario->revokePermissionTo($validatedData['permission']);
        return response()->json(['message' => 'Permiso removido exitosamente', 'user' => $usuario], 200);
    }

    public function storeRole(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = \Spatie\Permission\Models\Role::create(['name' => $validatedData['name']]);

        if (!empty($validatedData['permissions'])) {
            $role->syncPermissions($validatedData['permissions']);
        }

        return response()->json(['message' => 'Rol creado exitosamente', 'role' => $role], 201);
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $validatedData['name']]);

        if (!empty($validatedData['permissions'])) {
            $role->syncPermissions($validatedData['permissions']);
        }

        return response()->json(['message' => 'Rol actualizado exitosamente', 'role' => $role], 200);
    }

    public function storePermission(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $validatedData['name']]);
        return response()->json(['message' => 'Permiso creado exitosamente', 'permission' => $permission], 201);
    }

    /**
     * Listar roles de un usuario.
     */
    public function listUserRoles($id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = $usuario->getRoleNames();
        return response()->json(['roles' => $roles], 200);
    }

    /**
     * Listar permisos de un usuario.
     */
    public function listUserPermissions($id)
    {
        $usuario = Usuario::findOrFail($id);
        $permissions = $usuario->getAllPermissions();
        return response()->json(['permissions' => $permissions], 200);
    }

    /**
     * Listar todos los roles disponibles.
     */
    public function listRoles()
    {
        $roles = Role::get();
        return response()->json(['roles' => $roles], 200);
    }

    public function listRolesWithPermissions()
    {
        $roles = Role::with('permissions')->get();
        return response()->json(['roles' => $roles], 200);
    }

    /**
     * Listar todos los permisos disponibles.
     */
    public function listPermissions()
    {
        $permissions = Permission::get();
        return response()->json(['permissions' => $permissions], 200);
    }


    public function countRoles()
    {
        $roles = Role::withCount('users')->get();
        return response()->json(['roles' => $roles], 200);
    }

    public function showRole($id)
    {
        $role = Role::findOrFail($id)->load('permissions');
        return response()->json(['role' => $role], 200);
    }
}
