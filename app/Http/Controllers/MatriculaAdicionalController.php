<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MatriculaAdicional; // Asegúrate de importar tu modelo
use Illuminate\Support\Facades\Validator;
use App\Models\Horario;
use App\Models\Semestre;

class MatriculaAdicionalController extends Controller
{
    public function store(Request $request)
{
    // Validar los datos de entrada
    $validator = Validator::make($request->all(), [
        'estudiante_id' => 'required|integer',
        'especialidad_id' => 'required|integer',
        'motivo' => 'required|string',
        'justificacion' => 'required|string',
        'curso_id' => 'required|integer',
        'horario_id' => 'required|integer',
        'motivo_rechazo' => 'nullable|string',
    ]);

    // Si la validación falla, retorna un error
    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    // Crear una nueva matrícula adicional
    $matricula = MatriculaAdicional::create([
        'estudiante_id' => $request->estudiante_id,
        'especialidad_id' => $request->especialidad_id,
        'motivo' => $request->motivo,
        'justificacion' => $request->justificacion,
        'estado' => 'Pendiente DC', // O cualquier otro valor predeterminado que desees
        'motivo_rechazo' => $request->motivo_rechazo,
        'curso_id' => $request->curso_id,
        'horario_id' => $request->horario_id,
    ]);

    // Retornar la respuesta
    return response()->json($matricula, 201);
}

    public function getAll()
    {
        // Carga ansiosa para obtener los datos relacionados
        $matriculas = MatriculaAdicional::with(['estudiante.usuario', 'especialidad'])->get();
        return response()->json($matriculas);
    }

    public function getByEspecialidad(Request $request, $id)
{
    // Recoger los filtros de búsqueda y estado
    $search = $request->input('search', ''); // Campo de búsqueda
    $estado = $request->input('estado', null); // Estado para filtrar
    $perPage = $request->input('per_Page', 10); // Cantidad de elementos por página

    // Comenzar la consulta
    $query = MatriculaAdicional::with([
        'estudiante.usuario', 
        'especialidad', 
        'curso', 
        'horario', 
        'horario.docentes.usuario:id,nombre,apellido_paterno'
    ])
    ->where('especialidad_id', $id);

    // Aplicar el filtro de búsqueda si el campo no está vacío
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('estudiante.usuario', function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', '%' . $search . '%')
                          ->orWhere('apellido_paterno', 'like', '%' . $search . '%');
            })
            ->orWhereHas('curso', function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', '%' . $search . '%')
                          ->orWhere('cod_curso', 'like', '%' . $search . '%');
            });
        });
    }

    // Aplicar el filtro de estado si se ha seleccionado
    if ($estado) {
        $query->whereIn('estado', (array)$estado);
    }

    // Paginación
    $matriculas = $query->paginate($perPage);

    $result = $matriculas->map(function ($matricula) {
        $estudiante = $matricula->estudiante;
        return [
            'id' => $matricula->id,
            'codigo' => $estudiante->codigoEstudiante,
            'nombres' => $estudiante->usuario->nombre . ' ' . $estudiante->usuario->apellido_paterno . ' ' . $estudiante->usuario->apellido_materno,
            'ultimaModificacion' => Carbon::parse($matricula->updated_at)->format('d-m-Y'),
            'curso' => $matricula->curso->nombre,
            'horario' => $matricula->horario->nombre,
            'estado' => $matricula->estado,
        ];
    });

    return response()->json([
        'data' => $result,
        'pagination' => [
            'total' => $matriculas->total(),
            'current_page' => $matriculas->currentPage(),
            'last_page' => $matriculas->lastPage(),
            'per_page' => $matriculas->perPage(),
        ],
    ]);
}
    
public function getByEstudiante(Request $request, $estudianteId)
{
    // Recoger los filtros de búsqueda y estado
    $search = $request->input('search', ''); // Campo de búsqueda
    $estado = $request->input('estado', null); // Estado para filtrar
    $perPage = $request->input('per_Page', 10); // Cantidad de elementos por página

    // Comenzar la consulta
    $query = MatriculaAdicional::with([
        'estudiante.usuario', 
        'especialidad', 
        'curso', 
        'horario', 
        'horario.docentes.usuario:id,nombre,apellido_paterno',
    ])
    ->where('estudiante_id', $estudianteId);

    // Aplicar el filtro de búsqueda si el campo no está vacío
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('curso', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%')
                  ->orWhere('cod_curso', 'like', '%' . $search . '%');
            })
            ->orWhereHas('horario', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%');
            })
            ->orWhereHas('horario.docentes.usuario', function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%')
                  ->orWhere('apellido_paterno', 'like', '%' . $search . '%');
            });
        });
    }

    // Aplicar el filtro de estado si se ha seleccionado
    if ($estado) {
        $query->whereIn('estado', (array)$estado);
    }

    // Paginación
    $matriculas = $query->paginate($perPage);

    $result = $matriculas->map(function ($matricula) {
        return [
            'id' => $matricula->id,
            'clave' => $matricula->curso->cod_curso,
            'curso' => $matricula->curso->nombre,
            'horario' => $matricula->horario->nombre,
            'profesor' => isset($matricula->horario->docentes->first()->usuario) 
                ? $matricula->horario->docentes->first()->usuario->nombre . ' ' . $matricula->horario->docentes->first()->usuario->apellido_paterno 
                : 'Sin Profesor',
            'ultimaModificacion' => Carbon::parse($matricula->updated_at)->format('d-m-Y'),
            'estado' => $matricula->estado,
        ];
    });

    return response()->json([
        'data' => $result,
        'pagination' => [
            'total' => $matriculas->total(), // Total de filas
            'current_page' => $matriculas->currentPage(),
            'last_page' => $matriculas->lastPage(),
            'per_page' => $matriculas->perPage(),
        ],
    ]);
}


public function getByFacultad(Request $request, $facultadId)
{
    // Recoger los filtros de búsqueda y estado
    $search = $request->input('search', ''); // Campo de búsqueda
    $estado = $request->input('estado', null); // Estado para filtrar
    $perPage = $request->input('per_Page', 10); // Cantidad de elementos por página

    // Comenzar la consulta
    $query = MatriculaAdicional::with([
        'estudiante.usuario',
        'especialidad',
        'curso',
        'horario',
        'horario.docentes.usuario',
    ])
    ->whereHas('especialidad', function ($query) use ($facultadId) {
        $query->where('facultad_id', $facultadId);
    });

    // Aplicar el filtro de búsqueda si el campo no está vacío
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('estudiante.usuario', function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', '%' . $search . '%')
                          ->orWhere('apellido_paterno', 'like', '%' . $search . '%');
            })
            ->orWhereHas('curso', function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', '%' . $search . '%')
                          ->orWhere('cod_curso', 'like', '%' . $search . '%');
            });
        });
    }

    // Aplicar el filtro de estado si se ha seleccionado
    if ($estado) {
        $query->whereIn('estado', (array)$estado);
    }

    // Paginación
    $matriculas = $query->paginate($perPage);

    $result = $matriculas->map(function ($matricula) {
        return [
            'id' => $matricula->id,
            'codigo' => $matricula->estudiante->codigoEstudiante,
            'nombres' => $matricula->estudiante->usuario->nombre . ' ' . $matricula->estudiante->usuario->apellido_paterno,
            'ultimaModificacion' => $matricula->updated_at->format('d/m/Y'),
            'curso' => $matricula->curso->nombre,
            'especialidad' => $matricula->especialidad->nombre,
            'estado' => $matricula->estado,
        ];
    });

    return response()->json([
        'data' => $result,
        'pagination' => [
            'total' => $matriculas->total(),
            'current_page' => $matriculas->currentPage(),
            'last_page' => $matriculas->lastPage(),
            'per_page' => $matriculas->perPage(),
        ],
    ]);
}

public function getHorariosByCurso(Request $request, $cursoId)
    {
        // Obtener el semestre activo
        $semestreActivo = Semestre::where('estado', 'activo')->first();

        if (!$semestreActivo) {
            return response()->json(['message' => 'No hay semestre activo'], 404);
        }

        // Obtener solo los IDs y nombres de los horarios del curso que pertenecen al semestre activo
        $horarios = Horario::where('curso_id', $cursoId)
            ->where('semestre_id', $semestreActivo->id)
            ->select('id', 'nombre') // Solo selecciona el id y el nombre
            ->get();

        return response()->json($horarios);
    }

    public function getById($id)
{
    // Obtener la matrícula adicional por su ID
    $matricula = MatriculaAdicional::with([
        'estudiante.usuario', 
        'curso',
        'horario' // Asegúrate de incluir el horario si necesitas información de él
    ])->find($id);

    // Verificar si se encontró la matrícula
    if (!$matricula) {
        return response()->json(['message' => 'Matrícula adicional no encontrada'], 404);
    }

    // Preparar la respuesta
    $response = [
        'id' => $matricula->id,
        'codigoEstudiante' => $matricula->estudiante->codigoEstudiante,
        'nombreEstudiante' => $matricula->estudiante->usuario->nombre . ' ' . 
                             $matricula->estudiante->usuario->apellido_paterno . ' ' . 
                             $matricula->estudiante->usuario->apellido_materno,
        'correoEstudiante' => $matricula->estudiante->usuario->email,
        'nombreHorario' => $matricula->horario->nombre,
        'motivo' => $matricula->motivo,
        'justificacion' => $matricula->justificacion,
        'claveCurso' => $matricula->curso->cod_curso,
        'motivoRechazo' => $matricula->motivo_rechazo,
        'estado' => $matricula->estado, // Agregar estado
    ];

    return response()->json($response);
}

public function rechazar(Request $request, $id)
{
    // Validar la solicitud
    $request->validate([
        'motivo_rechazo' => 'required|string|max:255',
    ]);

    // Encontrar la matrícula
    $matricula = MatriculaAdicional::findOrFail($id);

    // Cambiar el estado y actualizar el motivo de rechazo
    $matricula->estado = 'Rechazado';
    $matricula->motivo_rechazo = $request->motivo_rechazo;

    // Guardar los cambios
    $matricula->save();

    return response()->json([
        'message' => 'Matrícula rechazada con éxito.',
        'matricula' => $matricula,
    ]);
}

public function aprobarPorDC($id)
{
    $matricula = MatriculaAdicional::findOrFail($id);

    // Verifica si el estado es 'Pendiente DC'
    if ($matricula->estado !== 'Pendiente DC') {
        return response()->json(['message' => 'El estado de la matrícula no es válido para esta acción.'], 400);
    }

    // Cambia el estado a 'Pendiente SA'
    $matricula->estado = 'Pendiente SA';
    $matricula->save();

    return response()->json(['message' => 'Matrícula actualizada a Pendiente SA.']);
}

public function aprobarPorSA($id)
{
    $matricula = MatriculaAdicional::findOrFail($id);

    // Verifica si el estado es 'Pendiente SA'
    if ($matricula->estado !== 'Pendiente SA') {
        return response()->json(['message' => 'El estado de la matrícula no es válido para esta acción.'], 400);
    }

    // Cambia el estado a 'Aprobado'
    $matricula->estado = 'Aprobado';
    $matricula->save();

    return response()->json(['message' => 'Matrícula aprobada.']);
}

}