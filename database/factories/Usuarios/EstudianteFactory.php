<?php

namespace Database\Factories;

use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuarios\Estudiante>
 */
class EstudianteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_especialidad = Especialidad::inRandomOrder()->first();

        return [
            'usuario_id' => Usuario::factory(),
            'codigoEstudiante' => $this->faker->unique()->randomNumber(8),
            'especialidad_id' => $random_especialidad->id ?? Especialidad::factory(),
        ];
    }
}
