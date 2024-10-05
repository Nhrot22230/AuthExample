<?php

namespace Database\Factories;

use App\Models\Docente;
use App\Models\Especialidad;
use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Especialidad>
 */
class EspecialidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word,
            'descripcion' => $this->faker->sentence,
            'facultad_id' => Facultad::all()->random()->id ?? null,
            // El director_id se manejará manualmente después de crear los docentes
            'director_id' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Especialidad $especialidad) {
            $docente = Docente::factory()->count(1)->create([
                'especialidad_id' => $especialidad->id,
            ]);

            $especialidad->update(['director_id' => $docente->first()->id]);
        });
    }
}
