<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Authorization\PermissionCategory;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Authorization\Role;
use App\Models\Authorization\Permission;

class RolePermissionsController extends Controller
{
    public function listUserRoles($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $rolesScopeUsuario = RoleScopeUsuario::where('usuario_id', $usuario->id)->get();
        $roles = $usuario->roles;

        $response = $roles->map(function ($role) use ($rolesScopeUsuario) {
            $roleScopesUsuario = $rolesScopeUsuario->where('role_id', $role->id);

            return [
                'id' => $role->id,
                'name' => $role->name,
                'scopes' => $role->scopes->map(function ($scope) use ($roleScopesUsuario) {
                    return [
                        'id' => $scope->id,
                        'name' => $scope->name,
                        'entities' => $roleScopesUsuario->where('scope_id', $scope->id)->map(function ($roleScopeUsuario) {
                            return [
                                'entity_id' => $roleScopeUsuario->entity_id,
                                'entity_type' => $roleScopeUsuario->entity_type,
                                'entity' => $roleScopeUsuario->entity,
                            ];
                        })->values()->toArray(),
                    ];
                })->toArray()
            ];
        });

        return response()->json($response, 200);
    }


    public function indexScopes()
    {
        $scopes = Scope::all();
        return response()->json($scopes, 200);
    }

    public function indexRoles()
    {
        $search = request('search', '');
        $per_page = request('per_page', 10);

        $roles = Role::withCount('users')
            ->where('name', 'like', "%$search%")
            ->paginate($per_page);

        return response()->json($roles, 200);
    }

    public function indexRolesScopes()
    {
        $roles = Role::with('scopes')->get();
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
        $role = Role::with(['permissions', 'scopes'])->find($id);

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
            'permissions.*' => 'exists:permissions,name',
            'scopes' => 'nullable|array',
            'scopes.*' => 'exists:scopes,id',
        ]);

        $role = Role::create($request->only('name'));

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        if ($request->has('scopes')) {
            $role->scopes()->sync($request->scopes);
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
            'permissions.*' => 'exists:permissions,name',
            'scopes' => 'nullable|array',
            'scopes.*' => 'exists:scopes,id',
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

    public function syncRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*.role_id' => 'required|exists:roles,id',
            'roles.*.scopes' => 'nullable|array',
            'roles.*.scopes.*.scope_id' => 'required|exists:scopes,id',
            'roles.*.scopes.*.entities' => 'required|array',
            'roles.*.scopes.*.entities.*' => 'required|integer|min:1',
        ]);

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        RoleScopeUsuario::where('usuario_id', $usuario->id)->delete();

        foreach ($request->roles as $roleData) {
            $role = Role::find($roleData['role_id']);
            $usuario->assignRole($role);

            foreach ($roleData['scopes'] as $scopeData) {
                $scope = Scope::find($scopeData['scope_id']);
                foreach ($scopeData['entities'] as $entityData) {
                    RoleScopeUsuario::create([
                        'role_id' => $role->id,
                        'scope_id' => $scope->id,
                        'usuario_id' => $usuario->id,
                        'entity_id' => $entityData,
                        'entity_type' => $scope->entity_type,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Roles correctamente asignados al usuario',
        ], 200);
    }

    public function syncPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'required|exists:permissions,id',
        ]);

        $usuario = Usuario::find($request->usuario_id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
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
