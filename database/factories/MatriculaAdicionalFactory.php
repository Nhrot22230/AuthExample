<?php

namespace Database\Factories;

use App\Models\MatriculaAdicional;
use App\Models\Estudiante;
use App\Models\Especialidad;
use App\Models\Curso;
use App\Models\Horario;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MatriculaAdicional>
 */
class MatriculaAdicionalFactory extends Factory
{
    protected $model = MatriculaAdicional::class;

    public function definition()
    {

        
        return [
            // Selecciona un estudiante aleatorio
            'estudiante_id' => function () {
                $estudiante = Estudiante::inRandomOrder()->first();
                return $estudiante->id;
             },

    // Usa la especialidad del estudiante seleccionado
            'especialidad_id' => function (array $attributes) {
                return Estudiante::find($attributes['estudiante_id'])->especialidad_id;
            },
            'horario_id' => function (array $attributes) {
                $horario = Horario::inRandomOrder()->first();
                return $horario->id;
            },
            'curso_id' => function (array $attributes) {
                return Horario::find($attributes['horario_id'])->curso_id;
            },

         
            'motivo' => $this->faker->sentence(),
            'justificacion' => $this->faker->paragraph(),
            'estado' => $this->faker->randomElement( ['Pendiente DC', 'Pendiente SA', 'Rechazado','Aprobado'] ),
            'motivo_rechazo' => $this->faker->optional()->sentence(), // Puede ser null
        ];
    }
}
