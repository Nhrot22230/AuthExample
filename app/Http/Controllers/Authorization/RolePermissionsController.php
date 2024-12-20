<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Authorization\Permission;
use App\Models\Authorization\PermissionCategory;
use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Universidad\Facultad;
use App\Models\Usuarios\Administrativo;
use App\Models\Usuarios\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionsController extends Controller
{
    public function listUserRoles($id): JsonResponse
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

    public function indexScopes(): JsonResponse
    {
        $scopes = Scope::all();
        return response()->json($scopes, 200);
    }

    public function indexRoles(): JsonResponse
    {
        $search = request('search', '');
        $per_page = request('per_page', 10);

        $roles = Role::withCount('users')
            ->where('name', 'like', "%$search%")
            ->paginate($per_page);

        return response()->json($roles, 200);
    }

    public function indexRolesScopes(): JsonResponse
    {
        $roles = Role::with('scopes')->get();
        return response()->json($roles, 200);
    }

    public function indexPermissions(): JsonResponse
    {
        $search = request('search', '');
        $permissions = Permission::with('permission_category')
            ->where('name', 'like', "%$search%")
            ->orderBy('permission_category_id')
            ->get();

        $simpleResp = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'permission_category' => $permission->permission_category?->name ?? "Sin categoría",
            ];
        });

        return response()->json($simpleResp, 200);
    }

    public function showRole($id): JsonResponse
    {
        $role = Role::with(['permissions', 'scopes'])->find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        return response()->json($role, 200);
    }

    public function storeRole(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'scopes' => 'nullable|array',
            'scopes.*' => 'exists:scopes,id',
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create($request->only('name'));
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            if ($request->has('scopes')) {
                $role->scopes([])->sync($request->scopes);
            }
            DB::commit();
            return response()->json([
                'message' => 'Rol creado correctamente',
                'role' => $role
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => "Error al crear el rol: {$e->getMessage()}"], 500);
        }
    }

    public function updateRole(Request $request, $id): JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $request->validate([
            'name' => "required|string|unique:roles,name,{$role->id}",
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
            $newScopes = $request->scopes;
            $role->scopes([])->sync($newScopes);
            RoleScopeUsuario::where('role_id', $role->id)
                ->whereNotIn('scope_id', $newScopes)
                ->delete();
        }

        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'role' => $role
        ], 200);
    }

    public function destroyRole($id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $role->delete();
        RoleScopeUsuario::where('role_id', $role->id)->delete();
        return response()->json(['message' => 'Rol eliminado'], 200);
    }

    public function syncRoles(Request $request, $id): JsonResponse
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*.role_id' => 'required|exists:roles,id',
            'roles.*.scopes' => 'nullable|array',
            'roles.*.scopes.*.scope_id' => 'required|exists:scopes,id',
            'roles.*.scopes.*.entities' => 'nullable|array',
            'roles.*.scopes.*.entities.*' => 'required|integer|min:1',
        ]);

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $rolesActuales = $usuario->roles->pluck('id')->toArray();
            // Obtener los nuevos roles de la solicitud
            $rolesNuevos = collect($request->roles)->pluck('role_id')->toArray();

            // Determinar los roles que deben eliminarse (roles que no están en la nueva lista)
            $rolesParaEliminar = array_diff($rolesActuales, $rolesNuevos);
            foreach ($rolesParaEliminar as $roleId) {
                $role = Role::find($roleId);
                $usuario->removeRole($role);  // Eliminar rol que ya no está en la solicitud
            }
            
            // Eliminar roles y scopes existentes
            RoleScopeUsuario::where('usuario_id', $usuario->id)->delete();

            foreach ($request->roles as $roleData) {
                $role = Role::find($roleData['role_id']);
                $usuario->assignRole($role);

                foreach ($roleData['scopes'] as $scopeData) {
                    $scope = Scope::find($scopeData['scope_id']);

                    // Verifica si `entities` está definido y es un arreglo
                    if (isset($scopeData['entities']) && is_array($scopeData['entities'])) {
                        foreach ($scopeData['entities'] as $entityData) {
                            RoleScopeUsuario::create([
                                'role_id' => $role->id,
                                'scope_id' => $scope->id,
                                'usuario_id' => $usuario->id,
                                'entity_id' => $entityData,
                                'entity_type' => $scope->entity_type,
                            ]);

                            // Verificar condiciones adicionales
                            if (
                                $scope->entity_type === Facultad::class &&
                                str_contains($role->name, 'secret') &&
                                Administrativo::whereHas('usuario', function ($query) use ($usuario) {
                                    $query->where('id', $usuario->id);
                                })->exists()
                            ) {
                                // Encontrar el administrativo asociado
                                $administrativo = Administrativo::whereHas('usuario', function ($query) use ($usuario) {
                                    $query->where('id', $usuario->id);
                                })->first();

                                // Modificar el facultad_id del administrativo
                                $administrativo->update(['facultad_id' => $entityData]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Roles correctamente asignados al usuario',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => "Error al asignar roles al usuario: {$e->getMessage()}"], 500);
        }
    }

    public function authUserPermissions(Request $request): JsonResponse
    {
        $usuario = $request->authUser;
        $permissions = $usuario->getAllPermissions();
        $response = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'permission_category' => PermissionCategory::find($permission->permission_category_id),
            ];
        });
        $uniqueCategories = $response->pluck('permission_category')->unique('access_path')->values()->pluck('access_path');
        return response()->json([
            'permissions' => $response,
            'access_paths' => $uniqueCategories,
        ], 200);
    }

    public function authUserRoles(Request $request): JsonResponse
    {
        $usuario = $request->authUser;
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $roleScopeUsuario = RoleScopeUsuario::with([
            'role',
            'scope',
            'entity',
        ])->where('usuario_id', $usuario->id)->get();
        
        $roles = $usuario->roles->map(function ($role) use ($roleScopeUsuario) {
            $roleScopes = $roleScopeUsuario->where('role_id', $role->id);
        
            return [
                'id' => $role->id,
                'name' => $role->name,
                'scopes' => $roleScopes->groupBy('scope_id')->map(function ($scopeGroup) {
                    $scope = $scopeGroup->first()->scope;

                    return [
                        'id' => $scope->id,
                        'name' => $scope->name,
                        'entities' => $scopeGroup->map(function ($roleScope) {
                            return $roleScope->entity;
                        })->unique('id')->values(),
                    ];
                })->values(),
            ];
        });        


        return response()->json($roles, 200);
    }
}
