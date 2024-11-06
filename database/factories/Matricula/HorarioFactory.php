<?php

namespace Database\Factories;

use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Matricula\Horario>
 */
class HorarioFactory extends Factory
{
    public function definition(): array
    {
        $curso = Curso::inRandomOrder()->first() ?? Curso::factory()->create();
        $semestre = Semestre::inRandomOrder()->first();

        return [
            'curso_id' => $curso ? $curso->id : Curso::factory(),
            'semestre_id' => $semestre ? $semestre->id : Semestre::factory(),
            'nombre' => $this->faker->unique()->sentence(3),
            'codigo' => strtoupper($this->faker->unique()->bothify('H##??')),
            'vacantes' => $this->faker->numberBetween(20, 45),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
