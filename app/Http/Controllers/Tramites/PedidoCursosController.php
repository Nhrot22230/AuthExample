<?php

namespace App\Http\Controllers\Tramites;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tramites\PedidoCursos;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use App\Models\Usuarios\Docente;

class PedidoCursosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener la cantidad de elementos por página y otros parámetros de la solicitud
        $perPage = $request->input('per_page', 10); // Número de resultados por página (predeterminado 10)
        $especialidadId = $request->input('especialidad_id', null); // Filtro por especialidad
        $estado = $request->input('estado', null); // Filtro por estado

        // Construir la consulta base
        $pedidos = PedidoCursos::with(['facultad', 'especialidad', 'semestre', 'planEstudio'])
            ->when($especialidadId, function ($query, $especialidadId) {
                // Filtrar por especialidad si se proporciona
                $query->where('especialidad_id', $especialidadId);
            })
            ->when($estado, function ($query, $estado) {
                // Filtrar por estado si se proporciona
                $query->where('estado', $estado);
            });

        // Ejecutar la consulta con paginación
        $pedidos = $pedidos->paginate($perPage);

        // Retornar la respuesta paginada en formato JSON
        return response()->json($pedidos, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getByFacultad(Request $request, $facultad)
    {
        // Obtener el número de resultados por página y otros filtros
        $perPage = $request->input('per_page', 10);
        $especialidadId = $request->input('especialidad_id', null);
        $estado = $request->input('estado', null);
        $searchTerm = $request->input('searchTerm', '');

        // Construir la consulta base para pedidos de la facultad específica
        $pedidos = PedidoCursos::with(['facultad', 'especialidad', 'semestre', 'planEstudio'])
            ->where('facultad_id', $facultad)
            ->when($especialidadId, function ($query, $especialidadId) {
                $query->where('especialidad_id', $especialidadId);
            })
            ->when($estado, function ($query, $estado) {
                $query->where('estado', $estado);
            });

        // Aplicar filtro de búsqueda en especialidad y en el director de carrera si se proporciona un searchTerm
        if (!empty($searchTerm)) {
            // Filtrar pedidos por el nombre de la especialidad
            $pedidos->whereHas('especialidad', function ($query) use ($searchTerm) {
                $query->where('nombre', 'like', '%' . $searchTerm . '%');
            });

            // Obtener los IDs de las especialidades que tienen un director de carrera que coincide con el searchTerm
            $directorEspecialidadIds = Docente::whereHas('usuario', function ($query) use ($searchTerm) {
                $query->where('nombre', 'like', '%' . $searchTerm . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $searchTerm . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            })
                ->whereHas('usuario.roles', function ($query) {
                    $query->where('name', 'director');
                })
                ->pluck('especialidad_id');

            // Filtrar los pedidos según los IDs de especialidades con el director de carrera que coincide
            $pedidos->orWhereIn('especialidad_id', $directorEspecialidadIds);
        }

        // Paginación
        $pedidos = $pedidos->paginate($perPage);

        // Agregar director de carrera a cada pedido
        foreach ($pedidos as $pedido) {
            $especialidad = $pedido->especialidad;

            // Obtener el director de carrera asociado a la especialidad
            $director = Docente::where('especialidad_id', $especialidad->id)
                ->whereHas('usuario.roles', function ($query) {
                    $query->where('name', 'director');
                })
                ->with('usuario')
                ->first();

            // Agregar el director de carrera al pedido
            $pedido->director_carrera = $director ? $director->usuario : null;
        }

        // Retornar los resultados paginados en formato JSON
        return response()->json($pedidos, 200);
    }

    public function enviarMultiplesPedidos(Request $request)
    {
        // Validar que se proporcione un array de IDs
        $request->validate([
            'pedido_ids' => 'required|array',
            'pedido_ids.*' => 'exists:pedido_cursos,id', // Asegura que cada ID exista en la tabla
        ]);

        // Obtener los IDs de pedidos desde la solicitud
        $pedidoIds = $request->input('pedido_ids');

        // Actualizar el estado y el campo enviado para cada pedido
        PedidoCursos::whereIn('id', $pedidoIds)
            ->update([
                'estado' => 'Enviado',
                'enviado' => 1,
            ]);

        return response()->json(['message' => 'Los pedidos han sido actualizados a enviado'], 200);
    }

    public function getCursosPorEspecialidad(Request $request, $especialidadId, $pedidoId)
    {
        // Obtener el número de resultados por página
        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('searchTerm', '');

        // Buscar el pedido de cursos de la especialidad e incluir el semestre
        $pedido = PedidoCursos::where('id', $pedidoId)
            ->with(['planEstudio', 'semestre']) // Incluimos la relación con semestre
            ->first();

        // Verificar si existe el pedido de cursos
        if (!$pedido) {
            return response()->json(['error' => 'Pedido de cursos no encontrado para la especialidad indicada'], 404);
        }

        // Obtener todos los cursos del pedido (obligatorios y electivos)
        $cursosQuery = $pedido->obtenerCursos();

        // Aplicar filtro de búsqueda si se proporciona un searchTerm
        if (!empty($searchTerm)) {
            $cursosQuery = $cursosQuery->filter(function ($curso) use ($searchTerm) {
                return stripos($curso->nombre, $searchTerm) !== false;
            });
        }

        // Paginar los resultados
        $cursosPaginated = $cursosQuery->forPage($request->input('page', 1), $perPage)->values();

        // Retornar los cursos junto con la información del semestre y de paginación
        return response()->json([
            'data' => $cursosPaginated,
            'total' => $cursosQuery->count(),
            'per_page' => $perPage,
            'current_page' => $request->input('page', 1),
            'semestre' => $pedido->semestre,
            'pedido_id' => $pedido->id,
            'pedido' => $pedido,
        ], 200);
    }

    public function destroyHorario($id)
    {
        // Buscar el horario por su ID
        $horario = \App\Models\Matricula\Horario::find($id);

        // Verificar si el horario existe
        if (!$horario) {
            return response()->json(['error' => 'Horario no encontrado'], 404);
        }

        // Eliminar relaciones dependientes
        $horario->jefePracticas()->delete(); // Elimina los registros relacionados de jefe de prácticas
        $horario->docentes()->detach();      // Desasocia los docentes
        $horario->usuarios()->detach();       // Desasocia los usuarios, si existe esta relación
        $horario->horarioEstudiantes()->delete(); // Elimina estudiantes asignados a este horario
        $horario->encuestas()->detach();      // Desasocia las encuestas

        // Finalmente, eliminar el horario
        $horario->delete();

        return response()->json(['message' => 'Horario eliminado correctamente'], 200);
    }

    public function destroyMultipleHorarios(Request $request)
    {
        // Validar que se proporcione un array de IDs de horarios
        $request->validate([
            'horario_ids' => 'required|array',
            'horario_ids.*' => 'exists:horarios,id' // Asegura que cada ID exista en la tabla de horarios
        ]);

        $horarioIds = $request->input('horario_ids');

        // Buscar los horarios por sus IDs
        $horarios = \App\Models\Matricula\Horario::whereIn('id', $horarioIds)->get();

        foreach ($horarios as $horario) {
            // Eliminar relaciones dependientes de cada horario
            $horario->jefePracticas()->delete(); // Eliminar registros de jefePracticas relacionados
            $horario->docentes()->detach();      // Desasociar docentes
            $horario->horarioEstudiantes()->delete(); // Eliminar estudiantes asignados a este horario
            $horario->encuestas()->detach();     // Desasociar encuestas

            // Para relaciones HasManyThrough, como usuarios y estudiantes, eliminamos los registros indirectamente
            foreach ($horario->usuarios as $usuario) {
                $usuario->pivot->delete(); // Eliminar el registro de la tabla intermedia
            }

            foreach ($horario->estudiantes as $estudiante) {
                $estudiante->pivot->delete(); // Eliminar el registro de la tabla intermedia
            }

            // Finalmente, eliminar el horario
            $horario->delete();
        }

        return response()->json(['message' => 'Horarios eliminados correctamente'], 200);
    }

    public function createHorarios(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'curso_id' => 'required|exists:cursos,id', // Asegura que el curso existe
            'semestre_id' => 'required|exists:semestres,id', // Asegura que el semestre existe
            'horarios' => 'required|array',
            'horarios.*.codigo' => 'required|string|max:10', // Valida el código del horario
            'horarios.*.vacantes' => 'required|integer|min:0', // Valida las vacantes como número entero positivo
            'horarios.*.oculto' => 'required|boolean', // Valida el campo oculto como booleano
        ]);

        $cursoId = $request->input('curso_id');
        $semestreId = $request->input('semestre_id');
        $horariosData = $request->input('horarios');

        $createdHorarios = [];

        foreach ($horariosData as $horarioData) {
            // Crear cada horario con los datos proporcionados
            $horario = \App\Models\Matricula\Horario::create([
                'curso_id' => $cursoId,
                'semestre_id' => $semestreId,
                'nombre' => 'nombre ejemplo',
                'codigo' => $horarioData['codigo'],
                'vacantes' => $horarioData['vacantes'],
                'oculto' => $horarioData['oculto'],
            ]);

            $createdHorarios[] = $horario;
        }

        return response()->json([
            'message' => 'Horarios creados correctamente',
            'data' => $createdHorarios
        ], 201);
    }

    public function removeCursosElectivos(Request $request, $pedidoId)
    {
        // Validar que se proporcione un array de IDs de cursos
        $request->validate([
            'curso_ids' => 'required|array',
            'curso_ids.*' => 'exists:cursos,id' // Asegura que cada ID exista en la tabla de cursos
        ]);
    
        // Obtener el pedido de cursos junto con el semestre
        $pedido = PedidoCursos::with('semestre')->find($pedidoId);
    
        // Verificar si el pedido existe
        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }
    
        // Filtrar los cursos electivos en el pedido
        $cursoIds = $request->input('curso_ids');
        $cursosElectivos = $pedido->cursosElectivosSeleccionados()->whereIn('curso_id', $cursoIds)->get();
    
        // Verificar que existan cursos electivos válidos en la lista
        if ($cursosElectivos->isEmpty()) {
            return response()->json(['error' => 'No se encontraron cursos electivos para eliminar en este pedido'], 400);
        }
    
        // Obtener el ID del semestre asociado al pedido
        $semestreId = $pedido->semestre_id;
    
        // Eliminar los horarios asociados a cada curso electivo en el semestre actual
        foreach ($cursosElectivos as $curso) {
            $curso->horarios()->where('semestre_id', $semestreId)->delete();
        }
    
        // Eliminar los cursos electivos seleccionados de la relación
        $pedido->cursosElectivosSeleccionados()->detach($cursoIds);
    
        return response()->json(['message' => 'Cursos electivos y horarios eliminados del pedido exitosamente'], 200);
    }

    public function getCursosElectivosPorEspecialidad(Request $request, $especialidadId, $pedidoId)
    {
        // Obtener el número de resultados por página
        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('searchTerm', '');

        // Buscar el pedido de cursos más reciente de la especialidad
        $pedido = PedidoCursos::where('id', $pedidoId)
                    ->with('planEstudio')
                    ->first();

        // Verificar si existe el pedido de cursos
        if (!$pedido) {
            return response()->json(['error' => 'Pedido de cursos no encontrado para la especialidad indicada'], 404);
        }

        // Obtener todos los cursos electivos del plan de estudios
        $cursosElectivos = $pedido->planEstudio->cursos()
            ->wherePivot('nivel', '0') // Solo cursos electivos
            ->get();

        // Obtener los IDs de los cursos electivos que ya están en el pedido
        $cursosElectivosEnPedidoIds = $pedido->cursosElectivosSeleccionados()->pluck('curso_id')->toArray();

        // Mapear los cursos electivos y agregar un atributo `en_pedido`
        $cursosElectivos = $cursosElectivos->map(function ($curso) use ($cursosElectivosEnPedidoIds) {
            $curso->en_pedido = in_array($curso->id, $cursosElectivosEnPedidoIds);
            return $curso;
        });

        // Aplicar filtro de búsqueda si se proporciona un searchTerm
        if (!empty($searchTerm)) {
            $cursosElectivos = $cursosElectivos->filter(function ($curso) use ($searchTerm) {
                return stripos($curso->nombre, $searchTerm) !== false || stripos($curso->cod_curso, $searchTerm) !== false;
            });
        }

        // Paginar los resultados manualmente
        $cursosPaginated = $cursosElectivos->forPage($request->input('page', 1), $perPage)->values();

        // Retornar los cursos electivos con la información de paginación
        return response()->json([
            'data' => $cursosPaginated,
            'total' => $cursosElectivos->count(),
            'per_page' => $perPage,
            'current_page' => $request->input('page', 1),
            'pedido_id' => $pedido->id, // Agregamos el ID del pedido en la respuesta
            'pedido' => $pedido, 
        ], 200);
    }

    public function addCursosElectivosToPedido(Request $request, $pedidoId)
    {
        // Validar los datos de entrada
        $request->validate([
            'curso_ids' => 'required|array',
            'curso_ids.*' => 'exists:cursos,id' // Asegura que cada ID existe en la tabla de cursos
        ]);
    
        // Buscar el pedido de cursos e incluir el plan de estudio
        $pedido = PedidoCursos::with('planEstudio.cursos')->find($pedidoId);
    
        // Verificar si el pedido existe
        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }
    
        // Obtener los IDs de los cursos electivos que ya están en el pedido
        $cursosElectivosExistentes = $pedido->cursosElectivosSeleccionados()->pluck('curso_id')->toArray();
    
        // Obtener los cursos electivos del plan de estudios que coincidan con los `curso_ids` y sean nivel 'E'
        $nuevosCursosElectivos = $pedido->planEstudio->cursos()
            ->wherePivot('nivel', '0')
            ->whereIn('cursos.id', $request->input('curso_ids'))
            ->whereNotIn('cursos.id', $cursosElectivosExistentes)
            ->get();
    
        // Agregar los cursos electivos al pedido con los atributos adicionales
        foreach ($nuevosCursosElectivos as $curso) {
            $pedido->cursosElectivosSeleccionados()->attach($curso->id, [
                'nivel' => '0',
                'creditosReq' => $curso->creditos
            ]);
        }
    
        return response()->json([
            'message' => 'Cursos electivos agregados al pedido correctamente',
            'pedido_id' => $pedidoId,
            'cursos_agregados' => $nuevosCursosElectivos->pluck('id')
        ], 201);
    }    

    public function markPedidoAsReceived($pedidoId)
    {
        // Buscar el pedido por su ID
        $pedido = PedidoCursos::find($pedidoId);
    
        // Verificar si el pedido existe
        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }
    
        // Actualizar el estado del pedido a "Recibido"
        $pedido->estado = 'Recibido';
        $pedido->save();
    
        return response()->json(['message' => 'Estado del pedido actualizado a Recibido'], 200);
    }

    public function updatePedidoStatus(Request $request, $pedidoId)
    {
        // Validar la entrada
        $request->validate([
            'estado' => 'required|in:Aprobado,Rechazado', // Asegura que el estado sea "Aprobado" o "Rechazado"
            'observacion' => 'nullable|string|max:255',   // Observación opcional con un máximo de 255 caracteres
        ]);
    
        // Buscar el pedido por su ID
        $pedido = PedidoCursos::find($pedidoId);
    
        // Verificar si el pedido existe
        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }
    
        // Actualizar el estado del pedido
        $pedido->estado = $request->input('estado');
        $pedido->observaciones = $request->input('observacion', $pedido->observaciones);
        $pedido->save();
    
        return response()->json([
            'message' => 'Estado del pedido actualizado a ' . $pedido->estado,
            'observacion' => $pedido->observaciones,
        ], 200);
    }    

    public function addCursosHorarios(Request $request, $pedidoId)
    {
        // Validar la estructura de entrada
        $request->validate([
            'cursos' => 'required|array',
            'cursos.*.codigo_curso' => 'required|string|exists:cursos,cod_curso', // Verifica que el código de curso exista
            'cursos.*.codigo_horario' => 'required|string|max:10', // Código del horario
            'cursos.*.vacantes' => 'required|integer|min:1', // Vacantes como número positivo
            'cursos.*.oculto' => 'required|in:SI,NO', // Campo oculto debe ser YES o NO
        ]);

        // Buscar el pedido y su plan de estudios asociado
        $pedido = PedidoCursos::with('planEstudio')->find($pedidoId);

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        $planEstudio = $pedido->planEstudio;

        // Verificar si el plan de estudios existe
        if (!$planEstudio) {
            return response()->json(['error' => 'El pedido no tiene un plan de estudios asociado'], 400);
        }

        $semestreId = $pedido->semestre_id;

        $cursosAgregados = [];
        foreach ($request->input('cursos') as $cursoData) {
            $codigoCurso = $cursoData['codigo_curso'];
            $codigoHorario = $cursoData['codigo_horario'];
            $vacantes = $cursoData['vacantes'];
            $oculto = $cursoData['oculto'] === 'SI';

            // Buscar el curso por su código
            $curso = Curso::where('cod_curso', $codigoCurso)->first();

            if (!$curso) {
                continue; // Si no existe el curso, lo omitimos
            }

            // Verificar si el curso ya está en el pedido
            $isInPedido = $pedido->obtenerCursos()->contains('id', $curso->id);

            if (!$isInPedido) {
                // Verificar si es un curso electivo del plan de estudios
                $isElectivo = $planEstudio->cursos()
                    ->where('id', $curso->id)
                    ->wherePivot('nivel', '0') // Asegura que sea electivo
                    ->exists();

                if (!$isElectivo) {
                    continue; // Si no es electivo válido, lo omitimos
                }

                // Agregar el curso electivo al pedido
                $pedido->cursosElectivosSeleccionados()->attach($curso->id, [
                    'nivel' => '0',
                    'creditosReq' => $curso->creditos,
                ]);
            }

            // Verificar si el horario ya existe por su código para este curso y semestre
            $horarioExistente = \App\Models\Matricula\Horario::where('curso_id', $curso->id)
                ->where('semestre_id', $semestreId)
                ->where('codigo', $codigoHorario)
                ->first();

            if ($horarioExistente) {
                // Actualizar vacantes y oculto del horario existente
                $horarioExistente->update([
                    'vacantes' => $vacantes,
                    'oculto' => $oculto,
                ]);

                $cursosAgregados[] = [
                    'curso_id' => $curso->id,
                    'horario_id' => $horarioExistente->id,
                    'accion' => 'actualizado',
                ];
            } else {
                // Crear un nuevo horario si no existe
                $horario = \App\Models\Matricula\Horario::create([
                    'curso_id' => $curso->id,
                    'semestre_id' => $semestreId,
                    'codigo' => $codigoHorario,
                    'vacantes' => $vacantes,
                    'oculto' => $oculto,
                    'nombre' => '',
                ]);

                $cursosAgregados[] = [
                    'curso_id' => $curso->id,
                    'horario_id' => $horario->id,
                    'accion' => 'creado',
                ];
            }
        }

        return response()->json([
            'message' => 'Cursos y horarios procesados correctamente',
            'data' => $cursosAgregados,
        ], 200);
    }

}
