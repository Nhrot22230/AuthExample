<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EstudianteController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');
        $especialidadId = request('especialidad_id', null);
        $facultadId = request('facultad_id', null);

        try {
            $estudiantes = Estudiante::with(['usuario', 'especialidad.facultad'])
                ->where(function ($query) use ($search) {
                    $query->whereHas('usuario', function ($subQuery) use ($search) {
                        $subQuery->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido_paterno', 'like', "%{$search}%")
                            ->orWhere('apellido_materno', 'like', "%{$search}%")
                            ->orWhere('email', 'like', '%' . "%{$search}%");
                    })
                        ->orWhere('codigoEstudiante', 'like', "%{$search}%");
                });

            if (!empty($especialidadId)) {
                $estudiantes->where('especialidad_id', $especialidadId);
            }

            if (!empty($facultadId)) {
                $estudiantes->whereHas('especialidad.facultad', function ($query) use ($facultadId) {
                    $query->where('id', $facultadId);
                });
            }

            $estudiantes = $estudiantes->paginate($perPage);

            Log::channel('audit-log')->info('Estudiantes listados', [
                'per_page' => $perPage,
                'search' => $search,
                'especialidad_id' => $especialidadId,
                'facultad_id' => $facultadId,
            ]);

            return response()->json($estudiantes, 200);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al listar estudiantes', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Error al listar estudiantes'], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'codigoEstudiante' => 'required|string|unique:estudiantes,codigoEstudiante',
            'especialidad_id' => 'required|exists:especialidades,id',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                $usuario = Usuario::firstOrCreate(
                    ['email' => $validatedData['email']],
                    [
                        'nombre' => $validatedData['nombre'],
                        'apellido_paterno' => $validatedData['apellido_paterno'] ?? '',
                        'apellido_materno' => $validatedData['apellido_materno'] ?? '',
                        'password' => Hash::make($validatedData['codigoEstudiante']),
                    ]
                );

                Estudiante::create([
                    'usuario_id' => $usuario->id,
                    'especialidad_id' => $validatedData['especialidad_id'],
                    'codigoEstudiante' => $validatedData['codigoEstudiante'],
                ]);
            });

            Log::channel('audit-log')->info('Estudiante creado exitosamente', [
                'data' => $validatedData,
            ]);

            return response()->json(['message' => 'Estudiante creado exitosamente'], 201);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al crear estudiante', [
                'data' => $validatedData,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Error al crear estudiante'], 500);
        }
    }

    public function show($codigo)
    {
        try {
            $estudiante = Estudiante::with(['usuario', 'especialidad.facultad'])
                ->where('codigoEstudiante', $codigo)
                ->first();

            if (!$estudiante) {
                Log::channel('errors')->error('Estudiante no encontrado', [
                    'codigoEstudiante' => $codigo,
                ]);
                return response()->json(['message' => 'Estudiante no encontrado'], 404);
            }

            Log::channel('audit-log')->info('Estudiante consultado', [
                'codigoEstudiante' => $codigo,
            ]);

            return response()->json($estudiante, 200);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al consultar estudiante', [
                'codigoEstudiante' => $codigo,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error al consultar estudiante'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $estudiante = Estudiante::with('usuario')->find($id);
        if (!$estudiante) {
            Log::channel('errors')->error('Estudiante no encontrado para actualización', [
                'estudiante_id' => $id,
            ]);
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => "required|email|unique:usuarios,email,{$estudiante->usuario->id}",
            'codigoEstudiante' => "required|string|unique:estudiantes,codigoEstudiante,{$estudiante->id}",
            'especialidad_id' => 'required|exists:especialidades,id',
        ]);

        try {
            DB::transaction(function () use ($validatedData, $estudiante) {
                $estudiante->usuario->fill([
                    'nombre' => $validatedData['nombre'],
                    'apellido_paterno' => $validatedData['apellido_paterno'] ?? '',
                    'apellido_materno' => $validatedData['apellido_materno'] ?? '',
                    'email' => $validatedData['email'],
                ])->save();
                $estudiante->fill([
                    'especialidad_id' => $validatedData['especialidad_id'],
                    'codigoEstudiante' => $validatedData['codigoEstudiante'],
                ])->save();
            });

            Log::channel('audit-log')->info('Estudiante actualizado', [
                'estudiante_id' => $id,
                'data' => $validatedData,
            ]);

            return response()->json(['message' => 'Estudiante actualizado exitosamente'], 200);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al actualizar estudiante', [
                'estudiante_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Error al actualizar estudiante'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $estudiante = Estudiante::find($id);
            if (!$estudiante) {
                Log::channel('errors')->error('Estudiante no encontrado para eliminación', [
                    'estudiante_id' => $id,
                ]);
                return response()->json(['message' => 'Estudiante no encontrado'], 404);
            }

            $estudiante->delete();

            Log::channel('audit-log')->info('Estudiante eliminado', [
                'estudiante_id' => $id,
            ]);

            return response()->json(['message' => 'Estudiante eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al eliminar estudiante', [
                'estudiante_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Error al eliminar estudiante'], 500);
        }
    }

    public function storeMultiple(Request $request)
    {
        try {
            $request->validate([
                'estudiantes' => 'required|array',
                'estudiantes.*.Codigo' => 'required|string|unique:estudiantes,codigoEstudiante',
                'estudiantes.*.Nombre' => 'required|string',
                'estudiantes.*.ApellidoPaterno' => 'nullable|string',
                'estudiantes.*.ApellidoMaterno' => 'nullable|string',
                'estudiantes.*.Email' => 'required|email|unique:usuarios,email',
                'estudiantes.*.Especialidad' => 'required|string|exists:especialidades,nombre',
            ]);
        } catch (\Exception $e) {
            Log::channel('errors')->error('Error al validar los datos de los estudiantes', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al validar los datos de los estudiantes: ' . $e->getMessage()], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->estudiantes as $estudianteData) {
                $usuario = Usuario::firstOrCreate(
                    ['email' => $estudianteData['Email']],
                    [
                        'nombre' => $estudianteData['Nombre'],
                        'apellido_paterno' => $estudianteData['ApellidoPaterno'] ?? '',
                        'apellido_materno' => $estudianteData['ApellidoMaterno'] ?? '',
                        'password' => Hash::make($estudianteData['Codigo']),
                    ]
                );

                $estudiante = new Estudiante();
                $estudiante->usuario_id = $usuario->id;
                $estudiante->codigoEstudiante = $estudianteData['Codigo'];
                $estudiante->especialidad_id = Especialidad::where('nombre', $estudianteData['Especialidad'])->first()->id;
                $usuario->estudiante()->save($estudiante);
            }
            DB::commit();
            Log::channel('errors')->info('Estudiantes guardados exitosamente', ['estudiantes' => $request->estudiantes]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('errors')->error('Error al guardar los datos de los estudiantes', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al guardar los datos de los estudiantes'], 500);
        }
    }
}
