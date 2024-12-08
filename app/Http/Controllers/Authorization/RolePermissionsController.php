<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Authorization\Permission;
use App\Models\Authorization\PermissionCategory;
use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Usuarios\Usuario;
use Illuminate\Support\Facades\Log;
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
                'scope' => $role->scope->map(function ($scope) use ($roleScopesUsuario) {
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
        $roles = Role::with('scope')->get();
        return response()->json($roles, 200);
    }

    public function indexPermissions(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $scopeId = $request->input('scope_id', null);

        $permissionsQuery = Permission::with(['permission_category', 'scope'])
            ->where('name', 'like', "%$search%");

        // Si se proporciona un `scope_id`, filtrar por ese `scope_id`
        if ($scopeId) {
            $permissionsQuery->where('scope_id', $scopeId);
        }

        $permissions = $permissionsQuery->orderBy('scope_id')->get();

        $response = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'permission_category' => $permission->permission_category?->name ?? "Sin categoría",
                'scope' => $permission->scope ? [
                    'id' => $permission->scope->id,
                    'name' => $permission->scope->name,
                ] : null,
            ];
        });

        return response()->json($response, 200);
    }


    public function showRole($id): JsonResponse
    {
        $role = Role::with(['permissions', 'scope'])->find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $response = [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ];
            }),
            'scope' => $role->scope ? [ // Aquí accedemos directamente al scope
                'id' => $role->scope->id,
                'name' => $role->scope->name,
            ] : null, // Verificamos si existe un scope relacionado
        ];

        return response()->json($response, 200);
    }


    public function storeRole(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'scope_id' => 'nullable|exists:scopes,id', // Relación con scope
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create($request->only('name') + ['scope_id' => $request->scope_id]);

            // Sincronizar permisos si se han proporcionado
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            // Asignar el scope al rol (uno a uno)
            if ($request->has('scope_id')) {
                $role->scope()->associate($request->scope_id);
            }

            $role->save(); // Guardamos el rol después de asociar el scope

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
            // No permitimos modificar el scope_id
            'scope_id' => 'nullable|exists:scopes,id',
        ]);

        DB::beginTransaction();
        try {
            // Solo actualizamos el nombre del rol
            $role->update($request->only('name'));

            // Sincronizar permisos si se han proporcionado
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            // No actualizamos el scope del rol
            // Si se pasa un scope_id, no se hace nada. El scope original sigue siendo el mismo.

            $role->save(); // Guardamos los cambios

            DB::commit();
            return response()->json([
                'message' => 'Rol actualizado correctamente',
                'role' => $role
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => "Error al actualizar el rol: {$e->getMessage()}"], 500);
        }
    }


    public function destroyRole($id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $role->delete();
        return response()->json(['message' => 'Rol eliminado'], 200);
    }

    public function syncRoles(Request $request, $id): JsonResponse
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*.role_id' => 'required|exists:roles,id',
            'roles.*.scopes' => 'nullable|array',
            'roles.scope_id' => 'required|exists:scopes,id',
            'roles.*.scopes.*.entities' => 'nullable|array',
            'roles.*.scopes.*.entities.*' => 'required|integer|min:1',
        ]);

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        DB::beginTransaction();
        try {

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

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $permissions = $usuario->getPermissionsAttribute();

        $formattedPermissions = $permissions->map(function ($permission) {
            $scope = $permission->scope;

            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'scope' => [
                    'id' => $scope->id,
                    'name' => $scope->name,
                    'access_path' => $scope->access_path,
                ],
            ];
        });

        $accessPaths = $permissions->pluck('scope.access_path')->unique()->values();

        return response()->json([
            'permissions' => $formattedPermissions,
            'access_paths' => $accessPaths,
        ], 200);
    }


    public function authUserRoles(Request $request): JsonResponse
    {
        $usuario = $request->authUser;

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Obtener los roles del usuario
        $roles = $usuario->roles;

        // Formatear los roles y sus scopes con permisos
        $formattedRoles = $roles->map(function ($role) {
            // Obtener los permisos asociados al rol
            $permissions = $role->permissions;

            // Obtener las rutas de acceso únicas asociadas a los permisos de los roles
            $accessPaths = $permissions->pluck('scope.access_path')->unique()->values();

            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $permissions->map(function ($permission) {
                    // Obtener el scope asociado a cada permiso
                    $scope = $permission->scope;

                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'scope' => [
                            'id' => $scope->id,
                            'name' => $scope->name,
                            'access_path' => $scope->access_path,  // Añadir el access_path
                        ],
                    ];
                }),
                'access_paths' => $accessPaths,  // Incluir las rutas de acceso
            ];
        });

        return response()->json($formattedRoles, 200);
    }
}
