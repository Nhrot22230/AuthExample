<?php

namespace App\Http\Controllers\GestionEspecialidad;

use App\Http\Controllers\Controller;
use App\Models\Matricula\Horario;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Estudiante;
use App\Models\Universidad\Curso;
use App\Models\Usuarios\Administrativo;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\JefePractica;
use Illuminate\Http\Request;

class GestionProfesoresJPsController extends Controller
{
    public function asignarDocentes(Request $request, $especialidad_id)
    {
        // Validar el request
        $request->validate([
            'datos' => 'required|array',
            'datos.*.codigo_curso' => 'required|string|exists:cursos,cod_curso',
            'datos.*.codigo_horario' => 'required|string|exists:horarios,codigo',
            'datos.*.codigo_docente' => 'required|string|exists:docentes,codigoDocente',
        ]);

        // Verificar que la especialidad exista
        $especialidad = Especialidad::find($especialidad_id);
        if (!$especialidad) {
            return response()->json(['error' => 'Especialidad no encontrada'], 404);
        }
        
        $docentes_agregados_correctamente = 0;
        $resultados = [];

        foreach ($request->input('datos') as $dato) {
            // Buscar curso por código y verificar que pertenezca a la especialidad
            $curso = Curso::where('cod_curso', $dato['codigo_curso'])
                ->where('especialidad_id', $especialidad_id)
                ->first();

            if (!$curso) {
                $resultados[] = [
                    'codigo_curso' => $dato['codigo_curso'],
                    'error' => 'El curso no pertenece a la especialidad especificada.',
                ];
                continue;
            }

            // Buscar el horario por código y curso
            $horario = Horario::where('codigo', $dato['codigo_horario'])
                ->where('curso_id', $curso->id)
                ->first();

            if (!$horario) {
                $resultados[] = [
                    'codigo_horario' => $dato['codigo_horario'],
                    'codigo_curso' => $dato['codigo_curso'],
                    'error' => 'El horario no pertenece al curso especificado.',
                ];
                continue;
            }

            // Buscar docente por código
            $docente = Docente::where('codigoDocente', $dato['codigo_docente'])->first();

            if (!$docente) {
                $resultados[] = [
                    'codigo_docente' => $dato['codigo_docente'],
                    'error' => 'docente no encontrado.',
                ];
                continue;
            }

            // Verificar si el docente ya está asociado al horario
            if ($horario->docentes()->where('docente_id', $docente->id)->exists()) {
                $resultados[] = [
                    'codigo_docente' => $dato['codigo_docente'],
                    'codigo_horario' => $dato['codigo_horario'],
                    'error' => 'El docente ya está asignado a este horario.',
                ];
                continue;
            }

            // Asociar el docente al horario
            $horario->docentes()->attach($docente->id);

            $resultados[] = [
                'codigo_docente' => $dato['codigo_docente'],
                'codigo_horario' => $dato['codigo_horario'],
                'mensaje' => 'Docente asignado correctamente al horario.',
            ];
            $docentes_agregados_correctamente++;
        }

        return response()->json([
            'message' => 'Proceso completado',
            'resultados' => $resultados,
            'docentes_agregados' => $docentes_agregados_correctamente,
        ]);
    }
    
    public function asignarJefesPractica(Request $request, $especialidad_id)
    {
        // Validar el request
        $request->validate([
            'datos' => 'required|array',
            'datos.*.codigo_curso' => 'required|string|exists:cursos,cod_curso',
            'datos.*.codigo_horario' => 'required|string|exists:horarios,codigo',
            'datos.*.codigo_persona' => 'required|string', // Código único de Docente, Estudiante o Administrativo
        ]);
        $especialidad = Especialidad::find($especialidad_id);
        if (!$especialidad) {
            return response()->json(['error' => 'Especialidad no encontrada'], 404);
        }

        $resultados = [];
        $jps_agregados_correctamente = 0;

        foreach ($request->input('datos') as $dato) {
            // Buscar el curso por código y verificar que pertenezca a la especialidad
            $curso = Curso::where('cod_curso', $dato['codigo_curso'])
                ->where('especialidad_id', $especialidad_id)
                ->first();

            if (!$curso) {
                $resultados[] = [
                    'codigo_curso' => $dato['codigo_curso'],
                    'error' => 'El curso no pertenece a la especialidad especificada.',
                ];
                continue;
            }

            // Buscar el horario por código y curso
            $horario = Horario::where('codigo', $dato['codigo_horario'])
                ->where('curso_id', $curso->id)
                ->first();

            if (!$horario) {
                $resultados[] = [
                    'codigo_horario' => $dato['codigo_horario'],
                    'codigo_curso' => $dato['codigo_curso'],
                    'error' => 'El horario no pertenece al curso especificado.',
                ];
                continue;
            }

            // Buscar al JP (Docente, Estudiante o Administrativo) por su código
            $usuario = $this->buscarUsuarioPorCodigo($dato['codigo_persona']);

            if (!$usuario) {
                $resultados[] = [
                    'codigo_persona' => $dato['codigo_persona'],
                    'error' => 'No se encontró una persona con este código.',
                ];
                continue;
            }

            // Verificar si el JP ya está asignado a otro horario del mismo curso
            $jpEnCurso = JefePractica::where('usuario_id', $usuario->id)
                ->whereHas('horario', function ($query) use ($curso) {
                    $query->where('curso_id', $curso->id);
                })->exists();

            if ($jpEnCurso) {
                $resultados[] = [
                    'codigo_persona' => $dato['codigo_persona'],
                    'codigo_horario' => $dato['codigo_horario'],
                    'error' => 'El JP ya está asignado a otro horario del mismo curso.',
                ];
                continue;
            }

            // Verificar si el JP ya está asignado a este horario
            $jpExistente = $horario->jefesPractica()->where('usuario_id', $usuario->id)->exists();

            if ($jpExistente) {
                $resultados[] = [
                    'codigo_persona' => $dato['codigo_persona'],
                    'codigo_horario' => $dato['codigo_horario'],
                    'mensaje' => 'El JP ya está asignado a este horario.',
                ];
                continue;
            }

            // Asignar el JP al horario
            JefePractica::create([
                'usuario_id' => $usuario->id,
                'horario_id' => $horario->id,
            ]);

            $resultados[] = [
                'codigo_persona' => $dato['codigo_persona'],
                'codigo_horario' => $dato['codigo_horario'],
                'mensaje' => 'JP asignado correctamente al horario.',
            ];
            $jps_agregados_correctamente++;
        }

        return response()->json([
            'message' => 'Proceso completado',
            'resultados' => $resultados,
            'jps_agregados' => $jps_agregados_correctamente,
        ]);
    }

    /**
     * Buscar usuario por código.
     */
    private function buscarUsuarioPorCodigo($codigo)
    {
        // Buscar en Docentes
        $docente = Docente::where('codigoDocente', $codigo)->first();
        if ($docente) {
            return $docente->usuario;
        }

        // Buscar en Estudiantes
        $estudiante = Estudiante::where('codigoEstudiante', $codigo)->first();
        if ($estudiante) {
            return $estudiante->usuario;
        }

        // Buscar en Administrativos
        $administrativo = Administrativo::where('codigoAdministrativo', $codigo)->first();
        if ($administrativo) {
            return $administrativo->usuario;
        }

        return null;
    }
}
