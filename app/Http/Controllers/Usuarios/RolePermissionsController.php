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
}
