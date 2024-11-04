<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Authorization\PermissionCategory;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionsController extends Controller
{
    public function indexRoles()
    {
        $search = request('search', '');
        $per_page = request('per_page', 10);

        $roles = Role::withCount('users')
            ->where('name', 'like', "%$search%")
            ->paginate($per_page);

        return response()->json($roles, 200);
    }

    public function indexPermissions()
    {
        $search = request('search', '');
        $permissions = Permission::where('name', 'like', "%$search%")->get();

        return response()->json($permissions, 200);
    }

    public function showRole($id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        return response()->json($role, 200);
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'scopes' => 'nullable|array',
            'scopes.*' => 'exists:scopes,id'
        ]);

        $role = Role::create($request->only('name'));

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role, 201);
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'scopes' => 'nullable|array',
            'scopes.*' => 'exists:scopes,id'
        ]);

        $role->update($request->only('name'));
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        if ($request->has('scopes')) {
            $role->scopes()->sync($request->scopes);
        }

        return response()->json($role, 200);
    }

    public function destroyRole($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $role->delete();
        return response()->json(['message' => 'Rol eliminado'], 200);
    }

    public function syncRoles(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'roles' => 'required|array',
            'roles.*.scope_id' => 'required|exists:scopes,id',
            'roles.*.role_id' => 'required|exists:roles,id',
            'roles.*.entity_type' => 'required|string|exists:scopes,entity_type',
            'roles.*.entity_id' => 'required|integer|min:1'
        ]);

        $usuario = Usuario::find($request->usuario_id);

        RoleScopeUsuario::where('usuario_id', $usuario->id)->delete();

        foreach ($request->roles as $roleData) {
            RoleScopeUsuario::create([
                'usuario_id' => $usuario->id,
                'role_id' => $roleData['role_id'],
                'scope_id' => $roleData['scope_id'],
                'entity_type' => $roleData['entity_type'],
                'entity_id' => $roleData['entity_id'],
            ]);
        }

        return response()->json([
            'message' => 'Roles correctamente asignados al usuario',
        ], 200);
    }

    public function syncPermissions(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'permissions' => 'required|array',
            'permissions.*' => 'required|exists:permissions,id',
        ]);

        $usuario = Usuario::find($request->usuario_id);
        $usuario->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Permisos correctamente asignados al usuario',
        ], 200);
    }

    public function authUserPermissions(Request $request)
    {
        $usuario = $request->authUser;
        $permissions = $usuario->getAllPermissions();
        $response = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'category' => PermissionCategory::find($permission->category_id),
            ];
        });
        $uniqueCategories = $response->pluck('category')->unique('access_path')->values()->pluck('access_path');
        return response()->json([
            'permissions' => $response,
            'access_paths' => $uniqueCategories,
        ], 200);
    }
}
