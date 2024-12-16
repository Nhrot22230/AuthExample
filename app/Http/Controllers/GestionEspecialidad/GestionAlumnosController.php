<?php

namespace App\Http\Controllers\GestionEspecialidad;

use App\Http\Controllers\Controller;
use App\Models\Matricula\Horario;
use App\Models\Matricula\HorarioEstudianteJp;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Estudiante;
use App\Models\Universidad\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                Log::info("Resultados: " . $resultados);
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
                Log::info("Resultados: " . $resultados);
                continue;
            }

            $alumno = Estudiante::where('codigoEstudiante', $dato['codigo_alumno'])->first();
            if (!$alumno) {
                $resultados[] = [
                    'codigo_alumno' => $dato['codigo_alumno'],
                    'error' => 'Alumno no encontrado',
                ];
                Log::info("Resultados: " . $resultados);
                continue;
            }

            // Verificar si ya está inscrito
            if ($horario->estudiantes()->where('estudiante_id', $alumno->id)->exists()) {
                $resultados[] = [
                    'codigo_alumno' => $dato['codigo_alumno'],
                    'codigo_horario' => $dato['codigo_horario'],
                    'error' => 'El alumno ya está inscrito en este horario',
                ];
                Log::info("Resultados: " . $resultados);
                continue;
            }

            // Verificar disponibilidad de vacantes
            if ($horario->vacantes <= 0) {
                $resultados[] = [
                    'codigo_horario' => $dato['codigo_horario'],
                    'error' => 'No hay vacantes disponibles en este horario',
                ];
                Log::info("Resultados: " . $resultados);
                continue;
            }

            // Inscribir al alumno en el horario
            $horario->estudiantes()->attach($alumno->id);
            $horario->decrement('vacantes');

            // Verificar si existen jefes de práctica asignados a este horario
            $jefesPractica = $horario->jefesPractica; // Obtener los jefes de práctica del horario

            if ($jefesPractica->isNotEmpty()) {
                // Crear el registro en la tabla HorarioEstudianteJp para cada jefe de práctica
                foreach ($jefesPractica as $jefePractica) {
                    // Asegurarse de que el alumno esté asociado al horario en la tabla intermedia 'estudiante_horario'
                    $estudianteHorario = $alumno->horarios()->where('horario_id', $horario->id)->first();

                    if ($estudianteHorario) {
                        HorarioEstudianteJp::create([
                            'estudiante_horario_id' => $estudianteHorario->id, // ID de la relación entre el estudiante y el horario
                            'jp_horario_id' => $jefePractica->id, // ID del jefe de práctica
                            'encuestaJP' => 0, // Establecer valor predeterminado de encuesta
                        ]);
                    }
                }
            }

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
