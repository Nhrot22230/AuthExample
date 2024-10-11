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
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $estudiantes = Estudiante::with(['usuario', 'especialidad.facultad'])
            ->where('codigoEstudiante', 'like', "%$search%")
            ->paginate($per_page);

        return response()->json($estudiantes, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'codigoEstudiante' => 'required|string|unique:estudiantes,codigoEstudiante',
            'especialidad_id' => 'required|exists:especialidades,id',
        ]);

        DB::transaction(function () use ($validatedData) {
            $usuario = Usuario::firstOrCreate(
                ['email' => $validatedData['email']],
                [
                    'nombre' => $validatedData['nombre'],
                    'apellido_paterno' => $validatedData['apellido_paterno'],
                    'apellido_materno' => $validatedData['apellido_materno'],
                    'password' => Hash::make($validatedData['codigoEstudiante']),
                ]
            );

            Estudiante::create([
                'usuario_id' => $usuario->id,
                'especialidad_id' => $validatedData['especialidad_id'],
                'codigoEstudiante' => $validatedData['codigoEstudiante'],
            ]);
        });

        return response()->json(['message' => 'Estudiante creado exitosamente'], 201);
    }


    public function show($codigo)
    {
        $estudiante = Estudiante::with(['usuario', 'especialidad.facultad'])
            ->where('codigoEstudiante', $codigo)
            ->first();

        if (!$estudiante) {
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }

        return response()->json($estudiante, 200);
    }

    public function update(Request $request, $codigo)
    {
        $estudiante = Estudiante::with('usuario')->where('codigoEstudiante', $codigo)->firstOrFail();
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email,' . $estudiante->usuario_id,
            'codigoEstudiante' => 'required|string|unique:estudiantes,codigoEstudiante,' . $estudiante->id,
            'especialidad_id' => 'required|exists:especialidades,id',
        ]);
        DB::transaction(function () use ($validatedData, $estudiante) {
            $estudiante->usuario->fill([
                'nombre' => $validatedData['nombre'],
                'apellido_paterno' => $validatedData['apellido_paterno'],
                'apellido_materno' => $validatedData['apellido_materno'],
                'email' => $validatedData['email'],
            ])->save();
            $estudiante->fill([
                'especialidad_id' => $validatedData['especialidad_id'],
                'codigoEstudiante' => $validatedData['codigoEstudiante'],
            ])->save();
        });
        return response()->json(['message' => 'Estudiante actualizado exitosamente'], 200);
    }
}
