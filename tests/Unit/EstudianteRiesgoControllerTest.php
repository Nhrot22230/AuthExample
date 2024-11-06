<?php

namespace Tests\Unit;

use App\Models\EstudianteRiesgo\EstudianteRiesgo;
use App\Models\EstudianteRiesgo\InformeRiesgo;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstudianteRiesgoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtener_estadisticas_informes()
    {
        // Todos estos datos son de ejemplo y usan suposiciones

        // Crear una especialidad para la prueba
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
            foreach ([7,9] as $semana) {
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

        // Simular la llamada a tu endpoint
        $response = $this->actingAs($this->getRandomUser())->get('/api/v1/estudiantesRiesgo/obtener_estadisticas_informes?IdEspecialidad=' . $especialidad->id);

        // Verificar que la respuesta es correcta
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'informe1' => [
                         'estadisticas' => [
                             ['label', 'value'],
                             ['label', 'value'],
                             ['label', 'value'],
                             ['label', 'value'],
                         ],
                         'pieData' => [
                             'labels',
                             'datasets' => [
                                 ['data']
                             ],
                         ],
                         'completitud',
                     ],
                     'informe2' => [
                         'estadisticas',
                         'pieData',
                         'completitud',
                     ],
                     'informe3' => [
                         'estadisticas',
                         'pieData',
                         'completitud',
                     ],
                     // ...
                 ]);
    }

    // Función para obtener un desempeño aleatorio
    private function getDesempenhoAleatorio()
    {
        $desempenhos = ['Mal', 'Regular', 'Bien'];
        return $desempenhos[array_rand($desempenhos)];
    }
}
