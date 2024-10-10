<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EstudianteController extends Controller
{
    public function index()
    {
        $page = request('page', 1);
        $per_page = request('per_page', 10);

        $estudiantes = Estudiante::with(['usuario', 'especialidad.facultad'])->paginate($per_page, ['*'], 'page', $page);
        return response()->json($estudiantes, 200);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'nombre' => 'required|string',
                'apellido_paterno' => 'required|string',
                'apellido_materno' => 'required|string',
                'email' => 'required|email|unique:usuarios,email',
                'codigoEstudiante' => 'required|string',
                'especialidad_id' => 'required|exists:especialidades,id',
            ]);

            $usuario = Usuario::firstOrCreate(
                ['email' => $request->email],
                [
                    'nombre' => $request->nombre,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'password' => Hash::make($request->codigoEstudiante),
                ]
            );

            $estudiante = Estudiante::create([
                'usuario_id' => $usuario->id,
                'especialidad_id' => $request->especialidad_id,
                'codigoEstudiante' => $request->codigoEstudiante,
            ]);

            DB::commit();
            return response()->json($estudiante, 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(['message' => 'Error al crear estudiante'], 500);
        }
    }

    public function show($codigo)
    {
        try {
            $estudiante = Estudiante::with(['usuario', 'especialidad.facultad'])->where('codigoEstudiante', $codigo)->first();
            if (!$estudiante) {
                return response()->json(['message' => 'Estudiante no encontrado'], 404);
            }

            return response()->json($estudiante, 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }
    }

    public function update(Request $request, $codigo)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'nombre' => 'required|string',
                'apellido_paterno' => 'required|string',
                'apellido_materno' => 'required|string',
                'email' => 'required|email|unique:usuarios,email',
                'especialidad_id' => 'required|exists:especialidades,id',
                'codigoEstudiante' => 'required|string',
            ]);

            $estudiante = Estudiante::with('usuario')->where('codigoEstudiante', $codigo)->firstOrFail();
            $estudiante->usuario->fill([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
            ])->save();

            $estudiante->fill([
                'especialidad_id' => $request->especialidad_id,
                'codigoEstudiante' => $request->codigoEstudiante,
            ])->save();
            DB::commit();
            return response()->json($estudiante, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(['message' => 'Error al actualizar estudiante'], 500);
        }
    }
}
