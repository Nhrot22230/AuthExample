<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class RoleScopeMiddleware
{
    public function handle(Request $request, Closure $next, $permission, $scopeType = null)
    {

        $token = JWTAuth::parseToken();
        $user = JWTAuth::authenticate();
        $payload = $token->getPayload();
        Log::channel('jwt-auth')->info('JWT Middleware', [
            'token' => $request->bearerToken(),
            'payload' => $payload,
            'auth' => $user
        ]);

        $hasPermission = $user->hasPermissionTo($permission);
        if ($scopeType && $hasPermission) {
            $scopeId = $request->input('scope_id');
            $hasPermission = $user->roles()->whereHas('scope', function ($query) use ($scopeType, $scopeId) {
                $query->where('type', $scopeType);

                if ($scopeId) {
                    $query->where('id', $scopeId);
                }
            })->exists();
        }

        if (!$hasPermission) {
            return response()->json([
                'message' => 'No tiene el permiso adecuado o el alcance requerido',
                'error_code' => 'PERMISSION_SCOPE_UNAUTHORIZED'
            ], 403);
        }

        $request->merge(['authUser' => $user]);
        Log::channel('jwt-auth')->info('JWT Middleware - User Authorized', [
            'request' => $request->all(),
            'user' => $user
        ]);
        return $next($request);
    }
}
