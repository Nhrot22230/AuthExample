<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Universidad\Facultad;
use App\Models\Usuarios\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacultadController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $facultades = Facultad::where('nombre', 'like', "%$search%")->paginate($per_page);

        return response()->json($facultades, 200);
    }

    public function indexAll()
    {
        $search = request('search', '');
        $facultades = Facultad::where('nombre', 'like', "%$search%")->get();
        return response()->json($facultades, 200);
    }

    public function show($entity_id)
    {
        $facultad = Facultad::find($entity_id);

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        // Buscar el secretario académico relacionado
        $secretarioData = RoleScopeUsuario::where('entity_type', Facultad::class)
            ->where('entity_id', $entity_id)
            ->first();

        // Si existe un secretario asociado, obtener su información de usuario
        if ($secretarioData) {
            $secretario = Usuario::find($secretarioData->usuario_id);

            // Agregar el secretario a la facultad como un atributo adicional
            $facultad->secretario = $secretario;
        } else {
            $facultad->secretario = null; // Si no hay secretario, asignar null
        }

        return response()->json($facultad, 200);
    }

    public function showByName($nombre)
    {
        $facultad = Facultad::with('especialidades')->where('nombre', $nombre)->first();

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        return response()->json($facultad, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'abreviatura' => 'required|string|max:255',
            'anexo' => 'nullable|string',
        ]);

        $facultad = new Facultad();
        $facultad->nombre = $validatedData['nombre'];
        $facultad->abreviatura = $validatedData['abreviatura'];
        $facultad->anexo = $validatedData['anexo'];
        $facultad->save();

        return response()->json($facultad, 201);
    }

    public function update(Request $request, $entity_id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'abreviatura' => 'required|string|max:255',
            'anexo' => 'nullable|string',
        ]);

        $facultad = Facultad::find($entity_id);

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        $facultad->nombre = $validatedData['nombre'];
        $facultad->abreviatura = $validatedData['abreviatura'];
        $facultad->anexo = $validatedData['anexo'];
        $facultad->save();

        return response()->json($facultad, 200);
    }

    public function destroy($entity_id)
    {
        try {
            $facultad = Facultad::find($entity_id);
            if (!$facultad) {
                return response()->json(['message' => 'Facultad no encontrada'], 404);
            }
            $facultad->delete();
            return response()->json($facultad, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'No se puede eliminar la facultad, ya que tiene especialidades o departamentos asociados'], 400);
        }
    }

    public function storeMultiple(Request $request)
    {
        // Validar el arreglo de facultades
        $validatedData = $request->validate([
            'facultades' => 'required|array|min:1',
            'facultades.*.nombre' => 'required|string|max:255|unique:facultades,nombre',
            'facultades.*.abreviatura' => 'required|string|max:255',
            'facultades.*.anexo' => 'nullable|string',
        ], [
            'facultades.required' => 'Debe enviar al menos una facultad.',
            'facultades.*.nombre.required' => 'El nombre de la facultad es obligatorio.',
            'facultades.*.nombre.unique' => 'El nombre de la facultad ":input" ya está registrado.',
            'facultades.*.abreviatura.required' => 'La abreviatura de la facultad es obligatoria.',
        ]);
    
        // Iniciar la transacción
        DB::beginTransaction();
    
        try {
            $nuevasFacultades = [];
            $errores = [];
    
            foreach ($validatedData['facultades'] as $facultadData) {
                // Verificar si la facultad ya existe (validación redundante)
                if (Facultad::where('nombre', $facultadData['nombre'])->exists()) {
                    $errores[] = [
                        'nombre_facultad' => $facultadData['nombre'],
                        'error' => "La facultad '{$facultadData['nombre']}' ya está registrada.",
                    ];
                    continue;
                }
    
                // Crear la nueva facultad
                $facultad = new Facultad();
                $facultad->nombre = $facultadData['nombre'];
                $facultad->abreviatura = $facultadData['abreviatura'];
                $facultad->anexo = $facultadData['anexo'] ?? null;
                $facultad->save();
    
                $nuevasFacultades[] = [
                    'nombre' => $facultad->nombre,
                    'mensaje' => "La facultad '{$facultad->nombre}' se registró correctamente.",
                ];
            }
    
            // Confirmar la transacción
            DB::commit();
    
            return response()->json([
                'message' => 'Proceso completado.',
                'facultades' => $nuevasFacultades,
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
