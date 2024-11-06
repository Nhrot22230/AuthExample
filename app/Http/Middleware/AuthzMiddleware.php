<?php

namespace App\Http\Middleware;

use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Universidad\Area;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Departamento;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Facultad;
use App\Models\Universidad\Seccion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthzMiddleware
{
    public function handle(Request $request, Closure $next, $permission, $entityType)
    {
        Log::channel('authz')->info("Checking permission: {$permission} on {$entityType}");

        $user = $request->authUser;
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 403);
        }

        if ($user->hasRole('Administrador')) {
            Log::channel('authz')->info("User: {$user->email} has ADMINISTRATOR access to {$permission} on {$entityType}");
            return $next($request);
        }

        $entityId = $request->route('id');
        $hasDirectAccess = RoleScopeUsuario::where('usuario_id', $user->id)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->exists();

        if ($hasDirectAccess && $user->can($permission)) {
            Log::channel('authz')->info("User: {$user->email} has DIRECT access to {$permission} on {$entityType}");
            return $next($request);
        }

        $allUserAccess = RoleScopeUsuario::where('usuario_id', $user->id)->get();
        foreach ($allUserAccess as $access) {
            if ($this->checkHierarchyAccess($access, $entityType, $entityId)) {
                Log::channel('authz')->info("User: {$user->email} has HIERARCHY access to {$permission} on {$entityType}");
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'No tienes permiso para realizar esta acción',
            'error_code' => 'FORBIDDEN_ACCESS'
        ], 403);
    }

    private function checkHierarchyAccess($access, $entityType, $entityId)
    {
        // 1. Facultad -> Especialidad
        if ($entityType === Especialidad::class && $access->entity_type === Facultad::class) {
            $especialidad = Especialidad::find($entityId);
            return $especialidad && $especialidad->facultad_id == $access->entity_id;
        }

        // 2. Facultad -> Departamento
        if ($entityType === Departamento::class && $access->entity_type === Facultad::class) {
            $departamento = Departamento::find($entityId);
            return $departamento && $departamento->facultad_id == $access->entity_id;
        }

        // 3. Departamento -> Sección
        if ($entityType === Seccion::class && $access->entity_type === Departamento::class) {
            $seccion = Seccion::find($entityId);
            return $seccion && $seccion->departamento_id == $access->entity_id;
        }

        // 4. Especialidad -> Curso
        if ($entityType === Curso::class && $access->entity_type === Especialidad::class) {
            $curso = Curso::find($entityId);
            return $curso && $curso->especialidad_id == $access->entity_id;
        }

        // 5. Especialidad -> Área
        if ($entityType === Area::class && $access->entity_type === Especialidad::class) {
            $area = Area::find($entityId);
            return $area && $area->especialidad_id == $access->entity_id;
        }

        // 6. Facultad -> Especialidad -> Curso
        if ($entityType === Curso::class && $access->entity_type === Facultad::class) {
            $curso = Curso::find($entityId);
            if ($curso && $curso->especialidad) {
                return $curso->especialidad->facultad_id == $access->entity_id;
            }
        }

        // 7. Facultad -> Especialidad -> Área
        if ($entityType === Area::class && $access->entity_type === Facultad::class) {
            $area = Area::find($entityId);
            if ($area && $area->especialidad) {
                return $area->especialidad->facultad_id == $access->entity_id;
            }
        }

        return false;
    }
}
