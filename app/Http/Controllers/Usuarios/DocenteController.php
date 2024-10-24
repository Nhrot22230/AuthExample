<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Especialidad;
use App\Models\Seccion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DocenteController extends Controller
{
    public function index()
    {
        // Recibimos los parámetros
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $seccionId = request('seccion_id', null);
        $especialidadId = request('especialidad_id', null);
        $tipo = request('tipo', null);

        $docentes = Docente::with(['usuario', 'seccion', 'especialidad'])
            ->where(function ($query) use ($search) {
                $query->whereHas('usuario', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhere('codigoDocente', 'like', '%' . $search . '%');
            });
        if ($seccionId) {
            $docentes->where('seccion_id', $seccionId);
        }
        if ($especialidadId) {
            $docentes->where('especialidad_id', $especialidadId);
        }
        if ($tipo) {
            $docentes->where('tipo', $tipo);
        }
        $docentes = $docentes->paginate($per_page);
        return response()->json($docentes, 200);
    }


    public function show($codigo)
    {
        $docente = Docente::with(['usuario', 'seccion', 'especialidad'])
            ->where('codigoDocente', $codigo)
            ->first();

        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        return response()->json($docente, 200);
    }

    public function update(Request $request, $codigo)
    {
        $docente = Docente::with('usuario')->where('codigoDocente', $codigo)->first();
        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $docente->usuario->id,
            'codigoDocente' => 'required|string|max:50|unique:docentes,codigoDocente,' . $docente->id,
            'password' => 'nullable|string|min:8',
            'tipo' => 'required|string',
            'especialidad_id' => 'required|integer|exists:especialidades,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
            'area_id' => 'nullable|integer|exists:areas,id',
        ]);

        DB::transaction(function () use ($validatedData, $docente) {
            $usuarioData = [
                'nombre' => $validatedData['nombre'],
                'apellido_paterno' => $validatedData['apellido_paterno'],
                'apellido_materno' => $validatedData['apellido_materno'],
                'email' => $validatedData['email'],
            ];
            if (!empty($validatedData['password'])) {
                $usuarioData['password'] = Hash::make($validatedData['password']);
            }
            $docente->usuario->update($usuarioData);
            $docenteData = [
                'codigoDocente' => $validatedData['codigoDocente'],
                'tipo' => $validatedData['tipo'],
                'especialidad_id' => $validatedData['especialidad_id'],
                'seccion_id' => $validatedData['seccion_id'],
            ];
            if (!empty($validatedData['area_id'])) {
                $docenteData['area_id'] = $validatedData['area_id'];
            }
            $docente->update($docenteData);
        });

        return response()->json(['message' => 'Docente actualizado exitosamente'], 200);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email',
            'codigoDocente' => 'required|string|max:50|unique:docentes,codigoDocente',
            'tipo' => 'required|string',
            'especialidad_id' => 'required|integer|exists:especialidades,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
            'area_id' => 'nullable|integer|exists:areas,id',
        ]);

        $usuario = Usuario::firstOrCreate(
            ['email' => $validatedData['email']],
            [
                'nombre' => $validatedData['nombre'],
                'apellido_paterno' => $validatedData['apellido_paterno'],
                'apellido_materno' => $validatedData['apellido_materno'],
                'password' => Hash::make($validatedData['codigoDocente']),
            ]
        );

        $docente = new Docente();
        $docente->usuario_id = $usuario->id;
        $docente->codigoDocente = $validatedData['codigoDocente'];
        $docente->tipo = $validatedData['tipo'];
        $docente->especialidad_id = $validatedData['especialidad_id'];
        $docente->seccion_id = $validatedData['seccion_id'];
        $docente->area_id = $validatedData['area_id'] ?? null;

        $usuario->docente()->save($docente);
        return response()->json(['message' => 'Docente creado exitosamente', 'docente' => $docente], 201);
    }

    public function destroy($codigo)
    {
        $docente = Docente::where('codigoDocente', $codigo)->first();
        if (!$docente) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        $docente->delete();
        return response()->json(['message' => 'Docente eliminado exitosamente'], 200);
    }

    public function storeMultiple(Request $request)
    {
        try {
            $request->validate([
                'docentes' => 'required|array',
                'docentes.*.Codigo' => 'required|string|max:50|unique:docentes,codigoDocente',
                'docentes.*.Nombre' => 'required|string|max:255',
                'docentes.*.ApellidoPaterno' => 'required|string|max:255',
                'docentes.*.ApellidoMaterno' => 'nullable|string|max:255',
                'docentes.*.Email' => 'required|email|unique:usuarios,email',
                'docentes.*.Especialidad' => 'required|string|exists:especialidades,nombre',
                'docentes.*.Seccion' => 'required|string|exists:secciones,nombre',
            ]);
        } catch (\Exception $e) {
            Log::channel('usuarios')->error('Error al validar los datos de los docentes', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al validar los datos de los docentes'], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->docentes as $docenteData) {
                $especialidad = Especialidad::where('nombre', $docenteData['Especialidad'])->firstOrFail();
                $seccion = Seccion::where('nombre', $docenteData['Seccion'])->firstOrFail();
                $usuario = Usuario::firstOrCreate(
                    ['email' => $docenteData['Email']],
                    [
                        'nombre' => $docenteData['Nombre'],
                        'apellido_paterno' => $docenteData['ApellidoPaterno'],
                        'apellido_materno' => $docenteData['ApellidoMaterno'] ?? '',
                        'password' => Hash::make($docenteData['Codigo']),
                    ]
                );

                Docente::create([
                    'usuario_id' => $usuario->id,
                    'codigoDocente' => $docenteData['Codigo'],
                    'tipo' => 'TPA',
                    'especialidad_id' => $especialidad->id,
                    'seccion_id' => $seccion->id,
                    'area_id' => null,
                ]);
            }
            DB::commit();
            return response()->json(['message' => 'Docentes cargados exitosamente'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al cargar docentes', 'error' => $e->getMessage()], 500);
        }
    }
}
