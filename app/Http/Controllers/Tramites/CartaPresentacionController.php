<?php

namespace App\Http\Controllers\Tramites;
use App\Http\Controllers\Controller;
use App\Models\Usuarios\Estudiante;
use App\Models\Solicitudes\CartaPresentacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartaPresentacionController extends Controller
{
    public function index(Request $request, $idEstudiante)
    {
        $estado = $request->input('estado', null);
        $perPage = $request->input('per_page', 10);

        $query = CartaPresentacion::where('estudiante_id', $idEstudiante);

        if ($estado) {
            $query->where('Estado', $estado);
        }

        $cartas = $query->paginate($perPage);

        if ($cartas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron cartas de presentación para este estudiante con el estado especificado.'
            ], 404);
        }

        return response()->json($cartas);
    }

    public function indexDocente($idDocente, Request $request)
    {
        $estado = $request->input('estado', null);
        $codigoCurso = $request->input('codigo_curso', null);
        $nombreCurso = $request->input('nombre_curso', null);
        $perPage = $request->input('per_page', 10);
    
        $query = CartaPresentacion::whereHas('horario', function($q) use ($idDocente, $codigoCurso, $nombreCurso) {
            $q->whereHas('docentes', function($q) use ($idDocente) {
                $q->where('docente_id', $idDocente);
            });
    
            if ($codigoCurso) {
                $q->whereHas('curso', function($subQuery) use ($codigoCurso) {
                    $subQuery->where('cod_curso', $codigoCurso);
                });
            }
    
            if ($nombreCurso) {
                $q->whereHas('curso', function($subQuery) use ($nombreCurso) {
                    $subQuery->where('nombre', 'like', '%' . $nombreCurso . '%');
                });
            }
        });
        
        if ($estado) {
            $query->where('Estado', $estado);
        }

        $cartas = $query->paginate($perPage);

        if ($cartas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron cartas de presentación para este docente con el estado especificado.'
            ], 404);
        }

        return response()->json($cartas);
    }

    public function indexDirector($idUsuario, Request $request)
    {
        $estado = $request->input('estado', null);
        $codigoCurso = $request->input('codigo_curso', null);
        $nombreCurso = $request->input('nombre_curso', null);
        $perPage = $request->input('per_page', 10);

        $esDirector = DB::table('role_scope_usuarios')
            ->where('usuario_id', $idUsuario)
            ->where('role_id', 4) // 4 es el ID para el rol de 'director'
            ->where('scope_id', 3) // 3 es el ID para el scope de 'Especialidad'
            ->exists();

        if (!$esDirector) {
            return response()->json([
                'message' => 'El usuario no tiene el rol de director en el alcance de especialidad.'
            ], 403);
        }

        $especialidad = DB::table('docentes')
            ->where('usuario_id', $idUsuario)
            ->value('especialidad_id');

        if (!$especialidad) {
            return response()->json([
                'message' => 'El usuario no está asociado a ninguna especialidad como director.'
            ], 403);
        }

        $query = CartaPresentacion::whereHas('horario', function($q) use ($especialidad, $codigoCurso, $nombreCurso) {
            $q->whereHas('curso', function($subQuery) use ($especialidad, $codigoCurso, $nombreCurso) {
                $subQuery->where('especialidad_id', $especialidad);
                
                if ($codigoCurso) {
                    $subQuery->where('cod_curso', $codigoCurso);
                }
                
                if ($nombreCurso) {
                    $subQuery->where('nombre', 'like', '%' . $nombreCurso . '%');
                }
            });
        });

        if ($estado) {
            $query->where('estado', $estado);
        }

        $cartas = $query->paginate($perPage);

        if ($cartas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron cartas de presentación para este director con el estado especificado.'
            ], 404);
        }

        return response()->json($cartas);
    }

    public function indexSecretaria($idUsuario, Request $request)
    {
        $estado = $request->input('estado', null);
        $especialidadId = $request->input('especialidad_id', null); // Filtro adicional de especialidad
        $codigoCurso = $request->input('codigo_curso', null);
        $nombreCurso = $request->input('nombre_curso', null);
        $perPage = $request->input('per_page', 10);

        $esSecretaria = DB::table('role_scope_usuarios')
            ->where('usuario_id', $idUsuario)
            ->where('role_id', 2) // 2 es el ID para el rol de 'secretario-academico'
            ->where('scope_id', 2) // 2 es el ID para el scope de 'Facultad'
            ->exists();

        if (!$esSecretaria) {
            return response()->json([
                'message' => 'El usuario no tiene el rol de secretario-academico en el alcance de facultad.'
            ], 403);
        }

        $facultad = DB::table('administrativos')
            ->where('usuario_id', $idUsuario)
            ->value('facultad_id');

        if (!$facultad) {
            return response()->json([
                'message' => 'El usuario no está asociado a ninguna facultad como secretaria académica.'
            ], 403);
        }

        $especialidades = DB::table('especialidades')
            ->where('facultad_id', $facultad)
            ->pluck('id');

        if ($especialidades->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron especialidades en la facultad asociada a esta secretaria académica.'
            ], 404);
        }

        $query = CartaPresentacion::whereHas('horario', function($q) use ($especialidades, $especialidadId, $codigoCurso, $nombreCurso) {
            $q->whereHas('curso', function($subQuery) use ($especialidades, $especialidadId, $codigoCurso, $nombreCurso) {
                if ($especialidadId) {
                    $subQuery->where('especialidad_id', $especialidadId);
                } else {
                    $subQuery->whereIn('especialidad_id', $especialidades);
                }
                if ($codigoCurso) {
                    $subQuery->where('cod_curso', $codigoCurso);
                }
                if ($nombreCurso) {
                    $subQuery->where('nombre', 'like', '%' . $nombreCurso . '%');
                }
            });
        });

        if ($estado) {
            $query->where('estado', $estado);
        }

        $cartas = $query->paginate($perPage);

        if ($cartas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron cartas de presentación para esta secretaria con los filtros especificados.'
            ], 404);
        }

        return response()->json($cartas);
    }


    public function create($idEstudiante)
    {
        $estudiante = Estudiante::findOrFail($idEstudiante);

        $horarios = $estudiante->horarios()->with('curso')->get();

        $cursos = $horarios->map(function ($horario) {
            return [
                'horario_id' => $horario->id,
                'curso_id' => $horario->curso->id,
                'curso_nombre' => $horario->curso->nombre,
                'curso_codigo' => $horario->curso->cod_curso
            ];
        })->unique('curso_id');

        return response()->json([
            'estudiante' => $estudiante,
            'cursos' => $cursos,
        ]);
    }

    public function store(Request $request, $idEstudiante)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'motivo' => 'required|string',
        ]);
        
        $carta = CartaPresentacion::create([
            'estudiante_id' => $idEstudiante,
            'horario_id' => $request->horario_id,
            'motivo' => $request->motivo,
            'estado' => 'Pendiente',
        ]);

        return response()->json([
            'message' => 'Solicitud de carta de presentación creada con éxito.',
            'carta' => $carta
        ], 201);
    }


}
