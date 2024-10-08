<?php

namespace Database\Factories;

use App\Models\Departamento;
use App\Models\Docente;
use App\Models\Seccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seccion>
 */
class SeccionFactory extends Factory
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
            'codigoSeccion' => $this->faker->unique()->randomNumber(8),
            'departamento_id' => Departamento::all()->random()->id ?? null,
        ];
    }
}
