<?php

namespace App\Http\Controllers\GestionEspecialidad;

use App\Http\Controllers\Controller;
use App\Models\Matricula\Horario;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Estudiante;
use App\Models\Universidad\Curso;
use Illuminate\Http\Request;

class GestionAlumnosController extends Controller
{
    public function asignarAlumnos(Request $request, $especialidad_id)
    {
        // Validar la entrada
        $request->validate([
            'datos' => 'required|array',
            'datos.*.codigo_curso' => 'required|string|exists:cursos,cod_curso',
            'datos.*.codigo_horario' => 'required|string|exists:horarios,codigo',
            'datos.*.codigo_alumno' => 'required|string|exists:estudiantes,codigoEstudiante',
        ]);

        // Verificar que la especialidad exista
        $especialidad = Especialidad::find($especialidad_id);
        if (!$especialidad) {
            return response()->json(['error' => 'Especialidad no encontrada'], 404);
        }
        $alumnos_agregados_correctamente = 0;
        $resultados = [];
        foreach ($request->input('datos') as $dato) {
            $curso = Curso::where('cod_curso', $dato['codigo_curso'])
                ->where('especialidad_id', $especialidad->id)
                ->first();

            if (!$curso) {
                $resultados[] = [
                    'codigo_curso' => $dato['codigo_curso'],
                    'error' => 'El curso no pertenece a esta especialidad',
                ];
                continue;
            }

            $horario = Horario::where('codigo', $dato['codigo_horario'])
                ->where('curso_id', $curso->id)
                ->first();

            if (!$horario) {
                $resultados[] = [
                    'codigo_curso' => $dato['codigo_curso'],
                    'codigo_horario' => $dato['codigo_horario'],
                    'error' => 'El horario no pertenece al curso especificado',
                ];
                continue;
            }

            $alumno = Estudiante::where('codigoEstudiante', $dato['codigo_alumno'])->first();
            if (!$alumno) {
                $resultados[] = [
                    'codigo_alumno' => $dato['codigo_alumno'],
                    'error' => 'Alumno no encontrado',
                ];
                continue;
            }

            // Verificar si ya está inscrito
            if ($horario->estudiantes()->where('estudiante_id', $alumno->id)->exists()) {
                $resultados[] = [
                    'codigo_alumno' => $dato['codigo_alumno'],
                    'codigo_horario' => $dato['codigo_horario'],
                    'error' => 'El alumno ya está inscrito en este horario',
                ];
                continue;
            }

            // Verificar disponibilidad de vacantes
            if ($horario->vacantes <= 0) {
                $resultados[] = [
                    'codigo_horario' => $dato['codigo_horario'],
                    'error' => 'No hay vacantes disponibles en este horario',
                ];
                continue;
            }

            // Inscribir al alumno en el horario
            $horario->estudiantes()->attach($alumno->id);
            $horario->decrement('vacantes');

            $resultados[] = [
                'codigo_alumno' => $dato['codigo_alumno'],
                'codigo_horario' => $dato['codigo_horario'],
                'mensaje' => 'Alumno inscrito correctamente',
            ];
            $alumnos_agregados_correctamente++;
        }

        return response()->json([
            'message' => 'Proceso completado',
            'resultados' => $resultados,
            'alumnos_agregados' => $alumnos_agregados_correctamente,
        ]);
    }    
}
