<?php

namespace App\Http\Controllers\Tramites;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tramites\PedidoCursos;
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

    public function getCursosPorEspecialidad(Request $request, $especialidadId)
    {
        // Obtener el número de resultados por página
        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('searchTerm', '');

        // Buscar el pedido de cursos de la especialidad
        $pedido = PedidoCursos::where('especialidad_id', $especialidadId)
                    ->with('planEstudio')
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

        // Retornar los cursos en formato JSON junto con la información de paginación
        return response()->json([
            'data' => $cursosPaginated,
            'total' => $cursosQuery->count(),
            'per_page' => $perPage,
            'current_page' => $request->input('page', 1),
        ], 200);
    }
}