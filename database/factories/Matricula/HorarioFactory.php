<?php

namespace Database\Factories\Matricula;

use App\Models\Matricula\Horario;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horario>
 */
class HorarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'curso_id' => Curso::factory(),
            'semestre_id' => Semestre::factory(),
            'nombre' => $this->faker->unique()->sentence(3),
            'codigo' => strtoupper($this->faker->unique()->bothify('H##??')),
            'vacantes' => $this->faker->numberBetween(20, 45),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
