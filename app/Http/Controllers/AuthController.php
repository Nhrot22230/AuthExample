<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Google\Client as GoogleClient;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $usuario = Usuario::with('estudiante', 'administrativo', 'docente')
            ->where('email', $credentials['email'])
            ->first();

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json(
            [
                'usuario' => $usuario,
                'access_token' => JWTAuth::fromUser($usuario),
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
            200
        );
    }

    public function register(Request $request)
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al registrar el usuario'], 400);
        }

        return response()->json(
            [
                'usuario' => $usuario,
                'access_token' => JWTAuth::fromUser($usuario),
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
            200
        );
    }

    public function googleLogin(Request $request)
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
                ],
                200
            );
        } catch (JWTException $e) {
            Log::channel('jwt-auth')->error('Google login JWTException', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (\Exception $e) {
            Log::channel('jwt-auth')->error('Google login Exception', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function me()
    {
        $usuario = JWTAuth::user();

        if ($usuario) {
            return response()->json(
                $usuario->load('estudiante', 'administrativo', 'docente'),
                200
            );
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json($token, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al refrescar el token'], 400);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Sesión cerrada'], 200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Sesión ya estaba cerrada'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al cerrar la sesión'], 400);
        }
    }

    private function validateRegisterRequest(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email|unique:usuarios',
            'password' => 'required|string',
        ]);
    }

    private function fillUsuario(Usuario $usuario, Request $request)
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
            ]);
            $db_usuario->assignRole('estudiante');
            return $db_usuario;
        }
        
        if (!$usuario->google_id) {
            $usuario->update(['google_id' => $google_id, 'picture' => $picture]);
        }

        return $usuario;
    }
}
