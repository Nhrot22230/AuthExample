<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Universidad\Departamento;
use App\Models\Universidad\Facultad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartamentoController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $departamentos = Departamento::with('facultad')
            ->where('nombre', 'like', "%$search%")
            ->orderBy('nombre')
            ->paginate($per_page);

        return response()->json($departamentos, 200);
    }

    public function indexAll()
    {
        $search = request('search', '');
        $departamentos = Departamento::with('facultad')
            ->where('nombre', 'like', "%$search%")
            ->orderBy('nombre')
            ->get();

        return response()->json($departamentos, 200);
    }

    public function show($entity_id)
    {
        $departamento = Departamento::find($entity_id);

        if (!$departamento) {
            return response()->json(['message' => 'Departamento no encontrado'], 404);
        }

        return response()->json($departamento, 200);
    }

    public function showByName($nombre)
    {
        $departamento = Departamento::with('facultad', 'secciones')->where('nombre', $nombre)->first();
        if (!$departamento) {
            return response()->json(['message' => 'Departamento no encontrado'], 404);
        }

        return response()->json($departamento, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'facultad_id' => 'required|exists:facultades,id',
        ]);

        $departamento = new Departamento();
        $departamento->nombre = $validatedData['nombre'];
        $departamento->descripcion = $validatedData['descripcion'];
        $departamento->facultad_id = $validatedData['facultad_id'];
        $departamento->save();

        return response()->json($departamento, 201);
    }

    public function update(Request $request, $entity_id)
    {
        $departamento = Departamento::find($entity_id);
        if (!$departamento) {
            return response()->json(['message' => 'Departamento no encontrado'], 404);
        }
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'facultad_id' => 'required|exists:facultades,id',
        ]);

        $departamento->nombre = $validatedData['nombre'];
        $departamento->descripcion = $validatedData['descripcion'];
        $departamento->facultad_id = $validatedData['facultad_id'];
        $departamento->save();

        return response()->json($departamento, 200);
    }

    public function destroy($entity_id)
    {
        $departamento = Departamento::find($entity_id);

        if (!$departamento) {
            return response()->json(['message' => 'Departamento no encontrado'], 404);
        }

        $departamento->delete();

        return response()->json($departamento, 200);
    }

    public function storeMultiple(Request $request)
    {
        // Validar el arreglo de departamentos
        $validatedData = $request->validate([
            'departamentos' => 'required|array|min:1',
            'departamentos.*.nombre' => 'required|string|max:255|unique:departamentos,nombre',
            'departamentos.*.descripcion' => 'nullable|string',
            'departamentos.*.facultad_nombre' => 'required|string|exists:facultades,nombre',
        ], [
            'departamentos.required' => 'Debe enviar al menos un departamento.',
            'departamentos.*.nombre.required' => 'El nombre del departamento es obligatorio.',
            'departamentos.*.nombre.unique' => 'El nombre del departamento ":input" ya existe.',
            'departamentos.*.facultad_nombre.required' => 'El campo "facultad" es obligatorio.',
            'departamentos.*.facultad_nombre.exists' => 'La facultad ":input" no está registrada.',
        ]);

        // Iniciar la transacción
        DB::beginTransaction();

        try {
            $nuevosDepartamentos = [];
            $errores = [];

            foreach ($validatedData['departamentos'] as $departamentoData) {
                // Buscar la facultad por su nombre
                $facultad = Facultad::where('nombre', $departamentoData['facultad_nombre'])->first();

                if (!$facultad) {
                    $errores[] = [
                        'nombre_departamento' => $departamentoData['nombre'],
                        'error' => "La facultad '{$departamentoData['facultad_nombre']}' no se encuentra registrada para el departamento '{$departamentoData['nombre']}'.",
                    ];
                    continue;
                }

                // Crear el nuevo departamento
                $departamento = new Departamento();
                $departamento->nombre = $departamentoData['nombre'];
                $departamento->descripcion = $departamentoData['descripcion'] ?? null;
                $departamento->facultad_id = $facultad->id;
                $departamento->save();

                $nuevosDepartamentos[] = [
                    'nombre' => $departamento->nombre,
                    'mensaje' => "El departamento '{$departamento->nombre}' se registró correctamente.",
                ];
            }

            // Confirmar la transacción
            DB::commit();

            return response()->json([
                'message' => 'Proceso completado.',
                'departamentos' => $nuevosDepartamentos,
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
