<?php

namespace Database\Factories\Solicitudes;

use App\Models\Matricula\Horario;
use App\Models\Solicitudes\MatriculaAdicional;
use App\Models\Usuarios\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatriculaAdicional>
 */
class MatriculaAdicionalFactory extends Factory
{
    protected $model = MatriculaAdicional::class;

    public function definition()
    {
        $est = Estudiante::factory()->create();
        $horario = Horario::factory()->create();
        
        return [
            'estudiante_id' => $est->id,
            'especialidad_id' => $est->especialidad_id,
            'horario_id' => $horario,
            'curso_id' => $horario->curso,
            'motivo' => $this->faker->sentence(),
            'justificacion' => $this->faker->paragraph(),
            'estado' => $this->faker->randomElement( ['Pendiente DC', 'Pendiente SA', 'Rechazado','Aprobado'] ),
            'motivo_rechazo' => $this->faker->optional()->sentence(), // Puede ser null
        ];
    }
}
