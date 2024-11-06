<?php

namespace Database\Seeders;

use App\Models\EstudianteRiesgo\EstudianteRiesgo;
use App\Models\EstudianteRiesgo\InformeRiesgo;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use Illuminate\Database\Seeder;

class EstudianteRiesgoSeeder extends Seeder
{
    public function run()
    {
        // Todos estos datos son de ejemplo y usan suposiciones

        // Crear una especialidad con ID 999
        $especialidad = Especialidad::factory()->create([
            'id' => 999, // Establecer el ID deseado
        ]);

        // Crear 5 estudiantes
        $estudiantes = Estudiante::factory()->count(5)->create();

        // Crear un curso y un docente para los estudiantes
        $curso = Curso::factory()->create();
        $docente = Docente::factory()->create();

        // Crear registros de estudiante_riesgo y sus informes
        foreach ($estudiantes as $estudiante) {
            // Crear el registro en estudiante_riesgo
            $estudianteRiesgo = EstudianteRiesgo::factory()->create([
                'codigo_estudiante' => $estudiante->codigoEstudiante,
                'codigo_curso' => $curso->id,
                'codigo_docente' => $docente->codigoDocente,
                'codigo_especialidad' => $especialidad->id,
                'horario' => '10:00 AM',
                'riesgo' => 'Alto',
                'fecha' => now(),
                'ciclo' => '2024-1',
                'nombre' => $estudiante->nombre,
                'observaciones' => 'Observaciones de prueba para estudiante ' . $estudiante->nombre,
                'desempenho' => 'Regular',
            ]);

            // Primer informe siempre respondido
            InformeRiesgo::factory()->create([
                'codigo_alumno_riesgo' => $estudianteRiesgo->id,
                'semana' => 5,
                'estado' => 'Respondida',
                'desempenho' => $this->getDesempenhoAleatorio(), // Desempeño aleatorio para el primer informe
            ]);

            $previousReportResponded = true; // Iniciamos suponiendo que el informe anterior está respondido
            foreach ([7, 9] as $semana) {
                // Si el informe anterior no fue respondido, este también estará como pendiente
                $estado = $previousReportResponded && (rand(0, 1) === 1) ? 'Respondida' : 'Pendiente';

                $informeRiesgoData = [
                    'codigo_alumno_riesgo' => $estudianteRiesgo->id,
                    'semana' => $semana,
                    'estado' => $estado,
                ];

                // Solo asignar un desempeño si el informe está respondido
                if ($estado === 'Respondida') {
                    $informeRiesgoData['desempenho'] = $this->getDesempenhoAleatorio();
                    $previousReportResponded = true; // El informe actual está respondido
                } else {
                    $informeRiesgoData['desempenho'] = null; // Sin desempeño si está pendiente
                    $previousReportResponded = false; // El informe actual no está respondido
                }

                InformeRiesgo::factory()->create($informeRiesgoData);
            }
        }
    }

    private function getDesempenhoAleatorio()
    {
        return collect(['Mal', 'Regular', 'Bien'])->random();
    }
}
