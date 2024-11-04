<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\Semestre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Horario>
 */
class HorarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Selecciona un curso y un semestre al azar de la base de datos
        $random_especialidad = Especialidad::where('facultad_id', 5)->inRandomOrder()->first();
        $curso = Curso::where('especialidad_id', $random_especialidad->id)->inRandomOrder()->first();
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
