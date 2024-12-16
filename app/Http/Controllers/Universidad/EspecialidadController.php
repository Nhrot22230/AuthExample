<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Facultad;
use App\Models\Usuarios\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EspecialidadController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $especialidades = Especialidad::where('nombre', 'like', "%$search%")->paginate($per_page);

        return response()->json($especialidades, 200);
    }

    public function indexAll()
    {
        $id_facultad = request('facultad_id', null);

        $especialidades = $id_facultad ?
            Especialidad::with('areas')->where('facultad_id', $id_facultad)->get()
            : Especialidad::with('areas')->get();


        return response()->json($especialidades, 200);
    }

    public function show($entity_id)
    {
        $especialidad = Especialidad::with('areas')->find($entity_id);

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        //Buscar el rol de director de carrera
        $rolDirector = Role::where('name', 'like', '%director%')->first();

        // Buscar el director de carrera relacionado
        $directorData = RoleScopeUsuario::where('entity_type', Especialidad::class)
            ->where('entity_id', $entity_id)
            ->where('role_id', $rolDirector->id)
            ->first();

        // Si existe un director de carrera asociado, obtener su información de usuario
        if ($directorData) {
            $director = Usuario::find($directorData->usuario_id);

            // Agregar el director de carrera a la facultad como un atributo adicional
            $especialidad->director = $director;
        } else {
            $especialidad->director = null; // Si no hay director de carrera, asignar null
        }

        //Buscar el rol de asistente de especialidad
        $rolAsistente = Role::where('name', 'like', '%asis%espe%')->first();

        // Buscar el asistente de especialidad relacionado
        $asistenteData = RoleScopeUsuario::where('entity_type', Especialidad::class)
            ->where('entity_id', $entity_id)
            ->where('role_id', $rolAsistente->id)
            ->first();

        // Si existe un asistente de especialidad asociado, obtener su información de usuario
        if ($asistenteData) {
            $asistente = Usuario::find($asistenteData->usuario_id);

            // Agregar el asistente de especialidad a la facultad como un atributo adicional
            $especialidad->asistente = $asistente;
        } else {
            $especialidad->asistente = null; // Si no hay asistente de especialidad, asignar null
        }

        return response()->json($especialidad, 200);
    }

    public function showByName($nombre)
    {
        $especialidad = Especialidad::with('areas')->where('nombre', $nombre)->first();

        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        return response()->json($especialidad, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'facultad_id' => 'required|integer|exists:facultades,id',
        ]);

        $especialidad = new Especialidad();
        $especialidad->nombre = $validatedData['nombre'];
        $especialidad->descripcion = $validatedData['descripcion'] ?? null;
        $especialidad->facultad_id = $validatedData['facultad_id'];
        $especialidad->save();

        return response()->json($especialidad, 201);
    }

    public function update(Request $request, $entity_id)
    {
        $especialidad = Especialidad::find($entity_id);
        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('errors')->error($e->getMessage());
            return response()->json(['message' => 'Error en los datos enviados: ' . $e->getMessage()], 422);
        }

        if ($validatedData['nombre'])
            $especialidad->nombre = $validatedData['nombre'];
        if ($validatedData['descripcion'])
            $especialidad->descripcion = $validatedData['descripcion'];

        $especialidad->save();

        return response()->json($especialidad, 200);
    }

    public function destroy($entity_id)
    {
        $especialidad = Especialidad::find($entity_id);
        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        $especialidad->delete();
        return response()->json(['message' => 'Especialidad eliminada exitosamente'], 200);
    }

    public function storeMultiple(Request $request)
    {
        // Validar el arreglo de especialidades
        $validatedData = $request->validate([
            'especialidades' => 'required|array|min:1',
            'especialidades.*.nombre' => 'required|string|max:255|unique:especialidades,nombre',
            'especialidades.*.descripcion' => 'nullable|string',
            'especialidades.*.facultad_nombre' => 'required|string|exists:facultades,nombre',
        ], [
            'especialidades.required' => 'Debe enviar al menos una especialidad.',
            'especialidades.*.nombre.required' => 'El nombre de la especialidad es obligatorio.',
            'especialidades.*.nombre.unique' => 'El nombre de la especialidad ":input" ya está registrado.',
            'especialidades.*.facultad_nombre.required' => 'El campo "facultad" es obligatorio.',
            'especialidades.*.facultad_nombre.exists' => 'La facultad ":input" no está registrada.',
        ]);

        // Iniciar la transacción
        DB::beginTransaction();

        try {
            $nuevasEspecialidades = [];
            $errores = [];

            foreach ($validatedData['especialidades'] as $especialidadData) {
                // Buscar la facultad por su nombre
                $facultad = Facultad::where('nombre', $especialidadData['facultad_nombre'])->first();

                if (!$facultad) {
                    $errores[] = [
                        'nombre_especialidad' => $especialidadData['nombre'],
                        'error' => "La facultad '{$especialidadData['facultad_nombre']}' no se encuentra registrada para la especialidad '{$especialidadData['nombre']}'.",
                    ];
                    continue;
                }

                // Crear la nueva especialidad
                $especialidad = new Especialidad();
                $especialidad->nombre = $especialidadData['nombre'];
                $especialidad->descripcion = $especialidadData['descripcion'] ?? null;
                $especialidad->facultad_id = $facultad->id;
                $especialidad->save();

                $nuevasEspecialidades[] = [
                    'nombre' => $especialidad->nombre,
                    'mensaje' => "La especialidad '{$especialidad->nombre}' se registró correctamente.",
                ];
            }

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'message' => 'Proceso completado.',
                'especialidades' => $nuevasEspecialidades,
                'errores' => $errores,
            ], 201);
        } catch (\Exception $e) {
            // En caso de error, hacemos rollback de la transacción
            DB::rollBack();

            return response()->json([
                'message' => 'Se produjo un error en el proceso. ' . $e->getMessage(),
            ], 500);
        }
    }
}
