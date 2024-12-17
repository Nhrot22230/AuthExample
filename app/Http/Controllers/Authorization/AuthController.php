<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Usuarios\Usuario;
use Exception;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $usuario = Usuario::with('estudiante', 'administrativo', 'docente')
            ->where('email', $credentials['email'])
            ->first();

        if (!$usuario) {
            return response()->json(['message' => 'El usuario no existe'], 404);
        }

        if (!Hash::check($credentials['password'], $usuario->password)) {
            return response()->json(['message' => 'Las credenciales son incorrectas'], 403);
        }

        return response()->json(
            [
                'usuario' => $usuario,
                'access_token' => JWTAuth::fromUser($usuario),
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'message' => 'Inicio de sesión exitoso',
            ],
            200
        );
    }

    public function register(Request $request): JsonResponse
    {
        $this->validateRegisterRequest($request);

        if (Usuario::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'El email ya está registrado'], 400);
        }

        $usuario = new Usuario();
        $this->fillUsuario($usuario, $request);
        $usuario->password = Hash::make($request->password);

        try {
            $usuario->save();
            $usuario->assignRole('estudiante');
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al registrar el usuario'], 400);
        }

        return response()->json(
            [
                'usuario' => $usuario,
                'access_token' => JWTAuth::fromUser($usuario),
                'refresh_token' => JWTAuth::refresh(JWTAuth::fromUser($usuario)),
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'message' => 'Registro exitoso',
            ],
            200
        );
    }

    public function googleLogin(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);
        Log::channel('jwt-auth')->info('Google login', ['token' => $request->token]);

        try {
            $client = new GoogleClient(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($request->token);

            if (!$payload) {
                return response()->json(['error' => 'Token is invalid'], 401);
            }

            $usuario = $this->findOrCreateUsuarioFromGooglePayload($payload);
            return response()->json(
                [
                    'usuario' => $usuario,
                    'access_token' => JWTAuth::fromUser($usuario),
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                    'message' => 'Inicio de sesión exitoso',
                ],
                200
            );
        } catch (JWTException $e) {
            Log::channel('jwt-auth')->error('Google login JWTException', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (Exception $e) {
            Log::channel('jwt-auth')->error('Google login Exception', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function obtenerMisUnidades(): JsonResponse
    {
        try {
            $usuario = JWTAuth::user();
            if (!$usuario) {
                return response()->json(['message' => 'No se pudo encontrar el usuario. Inicie sesión nuevamente.'], 401);
            }

            $roleScopeUsuario = RoleScopeUsuario::where('usuario_id', $usuario->id)->orderBy('entity_type')->orderBy('entity_id');

            if (!$roleScopeUsuario) {
                return response()->json(['message' => 'No se encontraron unidades para el usuario.'], 404);
            }

            $unidades = $roleScopeUsuario->get()->map(function ($roleScopeUsuario) {
                return [
                    'entity_type' => $roleScopeUsuario->entity_type,
                    'entity' => $roleScopeUsuario->entity
                ];
            });
            return response()->json($unidades, 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'El token ha expirado. Por favor, inicie sesión nuevamente.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'El token no es válido.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'No se pudo procesar el token.'], 401);
        } catch (Exception $e) {
            return response()->json(['message' => 'Ocurrió un error inesperado al obtener las unidades del usuario: ' . $e->getMessage()], 500);
        }
    }

    public function me(): JsonResponse
    {
        try {
            $usuario = JWTAuth::user();
            if (!$usuario) {
                return response()->json(['message' => 'No se pudo encontrar el usuario. Inicie sesión nuevamente.'], 401);
            }
            $usuario->load('permissions', 'roles.permissions', 'estudiante', 'administrativo', 'docente');
            return response()->json($usuario, 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'El token ha expirado. Por favor, inicie sesión nuevamente.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'El token no es válido.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'No se pudo procesar el token.'], 401);
        } catch (Exception $e) {
            return response()->json(['message' => 'Ocurrió un error inesperado al obtener el usuario.', 'error' => $e->getMessage()], 500);
        }
    }

    public function refresh(Request $request): JsonResponse
    {
        try {
            $refreshToken = $request->get('refresh_token');
            $token = JWTAuth::refresh($refreshToken);

            return response()->json([
                'usuario' => JWTAuth::user(),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'message' => 'Sesión refrescada con éxito',
            ], 200);

        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'El token ha expirado y no se puede refrescar. Inicie sesión nuevamente.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'El token no es válido.'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Error al refrescar el token.'], 400);
        } catch (Exception $e) {
            return response()->json(['message' => 'Ocurrió un error inesperado al refrescar el token.', 'error' => $e->getMessage()], 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Sesión cerrada con éxito'], 200);

        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'El token ya ha expirado.'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'El token no es válido. No se pudo cerrar la sesión.'], 400);
        } catch (JWTException $e) {
            return response()->json(['message' => 'La sesión ya estaba cerrada o el token no es válido.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al cerrar la sesión.', 'error' => $e->getMessage()], 500);
        }
    }

    private function validateRegisterRequest(Request $request): void
    {
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email|unique:usuarios',
            'password' => 'required|string',
        ]);
    }

    private function fillUsuario(Usuario $usuario, Request $request): void
    {
        $usuario->nombre = $request->nombre;
        $usuario->email = $request->email;
        $usuario->apellido_paterno = $request->apellido_paterno ?? null;
        $usuario->apellido_materno = $request->apellido_materno ?? null;
    }

    private function findOrCreateUsuarioFromGooglePayload(array $payload)
    {
        $email = $payload['email'];
        $nombre = $payload['given_name'];
        $apellido_paterno = $payload['family_name'] ?? null;
        $apellido_materno = $payload['middle_name'] ?? null;
        $google_id = $payload['sub'];
        $picture = $payload['picture'];
        $usuario = Usuario::with('estudiante', 'administrativo', 'docente')
            ->where('email', $email)
            ->first();

        if (!$usuario) {
            $db_usuario =  Usuario::create([
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'email' => $email,
                'google_id' => $google_id,
                'picture' => $picture,
                'estado' => 'activo',
            ]);
            $db_usuario->assignRole('docente');
            return $db_usuario;
        }

        if (!$usuario->google_id) {
            $usuario->update(['google_id' => $google_id, 'picture' => $picture]);
        }

        return $usuario;
    }
}
