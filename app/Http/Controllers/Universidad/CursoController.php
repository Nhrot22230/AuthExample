<?php

namespace App\Http\Controllers\Universidad;

use App\Http\Controllers\Controller;
use App\Models\Matricula\Horario;
use App\Models\Universidad\Curso;
use App\Models\Usuarios\Docente;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    //
    public function indexPaginated()
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');
        $especialidad_id = request('especialidad_id', null);

        $cursos = Curso::with('especialidad')
            ->where('nombre', 'like', "%$search%")
            ->where('cod_curso', 'like', "%$search%")
            ->when($especialidad_id, function ($query, $especialidad_id) {
                return $query->where('especialidad_id', $especialidad_id);
            })
            ->paginate($perPage);

        return response()->json(['cursos' => $cursos], 200);
    }

    public function index()
    {
        $search = request('search', '');
        $especialidad_id = request('especialidad_id', null);
        $cursos = Curso::with('especialidad')
            ->where('nombre', 'like', "%$search%")
            ->where('cod_curso', 'like', "%$search%")
            ->when($especialidad_id, function ($query, $especialidad_id) {
                return $query->where('especialidad_id', $especialidad_id);
            })
            ->get();
        return response()->json($cursos, 200);
    }

    public function getByCodigo($cod_curso)
    {
        $curso = Curso::with('especialidad')->where('cod_curso', $cod_curso)->first();
        if ($curso) {
            return response()->json($curso, 200);
        } else {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
    }

    public function show($id)
    {
        try {
            $curso = Curso::with('especialidad', 'planesEstudio')->findOrFail($id);
            return response()->json($curso, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'cod_curso' => 'required|string|max:6|unique:cursos,cod_curso',
            'nombre' => 'required|string|max:255',
            'creditos' => 'required|numeric|min:0',
            'estado' => 'nullable|string|in:activo,inactivo',
        ]);

        $curso = new Curso();
        $curso->especialidad_id = $validatedData['especialidad_id'];
        $curso->cod_curso = $validatedData['cod_curso'];
        $curso->nombre = $validatedData['nombre'];
        $curso->creditos = $validatedData['creditos'];
        $curso->estado = $validatedData['estado'] ?? 'activo';
        $curso->save();

        return response()->json($curso, 201);
    }

    public function update(Request $request, $id)
    {
        $curso = Curso::find($id);
        if (!$curso) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'cod_curso' => 'required|string|max:6|unique:cursos,cod_curso,' . $curso->id,
            'nombre' => 'required|string|max:255',
            'creditos' => 'required|numeric|min:0',
            'estado' => 'nullable|string|in:activo,inactivo',
        ]);

        $curso->especialidad_id = $validatedData['especialidad_id'];
        $curso->cod_curso = $validatedData['cod_curso'];
        $curso->nombre = $validatedData['nombre'];
        $curso->creditos = $validatedData['creditos'];
        if (isset($validatedData['estado'])) {
            $curso->estado = $validatedData['estado'];
        }
        $curso->save();

        return response()->json($curso, 200);
    }

    public function destroy($id)
    {
        $curso = Curso::find($id);
        if (!$curso) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $curso->delete();
        return response()->json(['message' => 'Curso eliminado'], 200);
    }

    public function obtenerDocentesPorCurso($cursoId)
    {
        // Obtener los horarios asociados al curso
        $horarios = Horario::where('curso_id', $cursoId)->with('docentes.usuario')->get();

        // Estructurar la respuesta con los docentes y su información
        $docentesPorHorario = $horarios->map(function ($horario) {
            return [
                'horario_id' => $horario->id,
                'horario_nombre' => $horario->nombre,
                'docentes' => $horario->docentes->map(function ($docente) {
                    $usuario = $docente->usuario;
                    return [
                        'nombre_completo' => $usuario->nombre . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
                        'docente_id' => $docente->id,
                    ];
                }),
            ];
        });

        return response()->json($docentesPorHorario);
    }

    public function obtenerHorariosPorCurso($cursoId)
    {
        // Obtener los horarios asociados al curso especificado
        $horarios = Horario::where('curso_id', $cursoId)
            ->select('id', 'nombre') // Seleccionamos solo los campos necesarios
            ->get();

        // Estructurar la respuesta para devolver los horarios
        $horariosData = $horarios->map(function ($horario) {
            return [
                'horario_id' => $horario->id,
                'horario_nombre' => $horario->nombre,
            ];
        });

        return response()->json($horariosData);
    }
    public function obtenerCursosPorDocente($docenteId)
    {
        // Obtener los cursos asociados al docente a través de la relación docente_curso
        $cursos = Docente::where('id', $docenteId)
            ->with(['cursos' => function ($query) {
                $query->select('cursos.id', 'cursos.nombre', 'cursos.cod_curso', 'cursos.creditos', 'cursos.estado', 'cursos.created_at', 'cursos.updated_at');
            }])
            ->first();

        // Verificar si el docente tiene cursos asignados
        if (!$cursos || $cursos->cursos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron cursos asignados para este docente.'], 404);
        }

        // Formatear la respuesta
        $cursosAsignados = $cursos->cursos->map(function ($curso) {
            return [
                'curso_id' => $curso->id,
                'curso_nombre' => $curso->nombre,
                'codigo_curso' => $curso->cod_curso,
                'creditos' => $curso->creditos,
                'estado' => $curso->estado,
                'fecha_creacion' => $curso->created_at,
                'ultima_actualizacion' => $curso->updated_at,
            ];
        });

        return response()->json($cursosAsignados);
    }

}
