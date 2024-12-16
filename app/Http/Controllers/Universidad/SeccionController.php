<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Departamento;
use App\Models\Universidad\Seccion;
use App\Models\Usuarios\Usuario;
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

    public function obtenerCursosHorarios($seccion_id, $semestre_id)
    {
        // Obtener los cursos que pertenecen a la sección y el semestre
        $cursos = Curso::where('seccion_id', $seccion_id)
                        ->whereHas('horarios', function($query) use ($semestre_id) {
                            $query->where('semestre_id', $semestre_id);
                        })
                        ->with(['horarios' => function($query) use ($semestre_id) {
                            $query->where('semestre_id', $semestre_id)
                                ->with(['jefePracticas', 'docentes']); // Cargar docentes y jefes de práctica
                        }])
                        ->get();

        // Si no hay cursos
        if ($cursos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron cursos solicitados para la sección en el semestre actual'], 404);
        }

        // Formatear los datos de los cursos, horarios, docentes y jefes de práctica
        $resultados = $cursos->map(function($curso) {
            return $curso->horarios->map(function($horario) use ($curso) {
                return [
                    'codigo_curso' => $curso->cod_curso,
                    'nombre_curso' => $curso->nombre,
                    'codigo_horario' => $horario->codigo,
                    'docentes' => $horario->docentes->map(function($docente) {
                        // Acceder al usuario del docente
                        $usuario = $docente->usuario;
                        
                        // Concatenar los campos disponibles
                        $nombreCompleto = trim(implode(' ', [
                            $usuario->nombre,
                            $usuario->apellido_paterno,
                            $usuario->apellido_materno
                        ]));

                        // Retornar el nombre completo del docente
                        return $nombreCompleto;
                    }),
                    'jefes_practica' => $horario->jefePracticas->map(function($jefe) {
                        // Acceder al usuario del jefe de práctica
                        $usuario = $jefe->usuario;

                        // Concatenar los campos disponibles
                        $nombreCompleto = trim(implode(' ', [
                            $usuario->nombre,
                            $usuario->apellido_paterno,
                            $usuario->apellido_materno
                        ]));

                        // Retornar el nombre completo del jefe de práctica
                        return $nombreCompleto;
                    }),
                ];
            });
        })->flatten(1);

        // Retornar los cursos con sus horarios, docentes y jefes de práctica
        return response()->json($resultados, 200);
    }

    public function show($entity_id)
    {
        $seccion = Seccion::find($entity_id);

        if (!$seccion) {
            return response()->json(['message' => 'Seccion no encontrada'], 404);
        }

        //Buscar el rol de coordinador de sección
        $rolCoordinador = Role::where('name', 'like', '%coord%secc%')->first();

        // Buscar el coordinador de sección relacionado
        $coordinadorData = RoleScopeUsuario::where('entity_type', Seccion::class)
            ->where('entity_id', $entity_id)
            ->where('role_id', $rolCoordinador->id)
            ->first();

        // Si existe un coordinador de sección asociado, obtener su información de usuario
        if ($coordinadorData) {
            $coordinador = Usuario::find($coordinadorData->usuario_id);

            // Agregar el coordinador de sección a la sección como un atributo adicional
            $seccion->coordinador = $coordinador;
        } else {
            $seccion->coordinador = null; // Si no hay coordinador de sección, asignar null
        }

        //Buscar el rol de asistente de sección
        $rolAsistente = Role::where('name', 'like', '%asis%secc%')->first();

        // Buscar el asistente de sección relacionado
        $asistenteData = RoleScopeUsuario::where('entity_type', Seccion::class)
            ->where('entity_id', $entity_id)
            ->where('role_id', $rolAsistente->id)
            ->first();

        // Si existe un asistente de sección asociado, obtener su información de usuario
        if ($asistenteData) {
            $asistente = Usuario::find($asistenteData->usuario_id);

            // Agregar el asistente de sección a la sección como un atributo adicional
            $seccion->asistente = $asistente;
        } else {
            $seccion->asistente = null; // Si no hay asistente de sección, asignar null
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
