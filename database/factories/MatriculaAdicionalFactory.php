<?php

namespace Database\Factories;

use App\Models\MatriculaAdicional;
use App\Models\Estudiante;
use App\Models\Especialidad;
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

            'motivo' => $this->faker->sentence(),
            'justificacion' => $this->faker->paragraph(),
            'estado' => $this->faker->randomElement(['pendiente', 'pendiente1','aprobado', 'rechazado']),
            'motivo_rechazo' => $this->faker->optional()->sentence(), // Puede ser null
        ];
    }
}
