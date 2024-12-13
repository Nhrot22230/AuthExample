<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Universidad\Departamento;
use App\Models\Universidad\Seccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SeccionController extends Controller
{
    //
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $secciones = Seccion::where('nombre', 'like', "%$search%")->paginate($per_page);

        return response()->json($secciones, 200);
    }

    public function indexAll()
    {
        $id_departamento = request('id_departamento', null);
        if ($id_departamento) {
            $secciones = Seccion::where('departamento_id', $id_departamento)->get();
        } else {
            $secciones = Seccion::all();
        }

        return response()->json($secciones, 200);
    }

    public function show($entity_id)
    {
        $seccion = Seccion::find($entity_id);

        if (!$seccion) {
            return response()->json(['message' => 'Seccion no encontrada'], 404);
        }

        return response()->json($seccion, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'departamento_id' => 'required|integer|exists:departamentos,id',
        ]);

        $seccion = new Seccion();
        $seccion->nombre = $validatedData['nombre'];
        $seccion->departamento_id = $validatedData['departamento_id'];
        $seccion->save();

        return response()->json($seccion, 201);
    }

    public function update(Request $request, $entity_id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'departamento_id' => 'required|integer|exists:departamentos,id',
        ]);

        $seccion = Seccion::find($entity_id);
        if (!$seccion) {
            return response()->json(['message' => 'Seccion no encontrada'], 404);
        }
        $seccion->nombre = $validatedData['nombre'];
        $seccion->departamento_id = $validatedData['departamento_id'];
        $seccion->save();
        return response()->json($seccion, 200);
    }

    public function destroy($entity_id)
    {
        $seccion = Seccion::find($entity_id);
        if (!$seccion) {
            return response()->json(['message' => 'Seccion no encontrada'], 404);
        }
        $seccion->delete();
        return response()->json(['message' => 'Seccion eliminada'], 200);
    }

    public function storeMultiple(Request $request)
    {
        // Validar el arreglo de secciones
        $validatedData = $request->validate([
            'secciones' => 'required|array|min:1',
            'secciones.*.nombre' => 'required|string|max:255|unique:secciones,nombre',
            'secciones.*.departamento_nombre' => 'required|string',
        ], [
            'secciones.*.departamento_nombre.required' => 'El campo "departamento" es obligatorio.',
            'secciones.*.departamento_nombre.string' => 'El campo "departamento" debe ser una cadena de texto.',
        ]);

        // Iniciar la transacción
        DB::beginTransaction();

        try {
            $nuevasSecciones = [];

            foreach ($validatedData['secciones'] as $seccionData) {
                // Buscar el departamento por su nombre
                $departamento = Departamento::where('nombre', $seccionData['departamento_nombre'])->first();

                if (!$departamento) {
                    // Si el departamento no existe, hacemos rollback y lanzamos el error
                    return response()->json([
                        'message' => "El departamento '{$seccionData['departamento_nombre']}' no se encuentra registrado para la sección '{$seccionData['nombre']}'. Debe registrarse primero.",
                    ], 422);
                }

                // Crear la nueva seccion
                $seccion = new Seccion();
                $seccion->nombre = $seccionData['nombre'];
                $seccion->departamento_id = $departamento->id;
                $seccion->save();

                $nuevasSecciones[] = $seccion;
            }

            // Si todo fue exitoso, se confirma la transacción
            DB::commit();

            return response()->json([
                'message' => 'Proceso completado.',
                'secciones' => $nuevasSecciones,
            ], 201);

        } catch (\Exception $e) {
            // En caso de error, hacemos rollback de la transacción
            DB::rollBack();

            return response()->json([
                'message' => 'Se produjo un error en el proceso. ' . $e->getMessage(),
            ], 500); // Aquí puedes personalizar el mensaje de error general si lo deseas
        }
    }


}
