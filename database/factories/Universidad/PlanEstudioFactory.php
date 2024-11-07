<?php

namespace Database\Factories\Universidad;

use App\Models\Universidad\Especialidad;
use App\Models\Universidad\PlanEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanEstudio>
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
            'especialidad_id' => Especialidad::factory(),
            'estado' => 'inactivo',
        ];
    }
}
