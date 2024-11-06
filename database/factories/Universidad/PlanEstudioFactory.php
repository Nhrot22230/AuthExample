<?php

namespace Database\Factories;

use App\Models\Universidad\Especialidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Universidad\PlanEstudio>
 */
class PlanEstudioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cantidad_semestres' => 10,
            'especialidad_id' => Especialidad::inRandomOrder()->first() ?? Especialidad::factory()->create(),
            'estado' => 'inactivo',
        ];
    }
}
