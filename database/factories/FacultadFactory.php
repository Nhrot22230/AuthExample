<?php

namespace Database\Factories;

use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Facultad>
 */
class FacultadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_departamento = Departamento::inRandomOrder()->first();

        return [
            'nombre' => $this->faker->word,
            'abreviatura' => $this->faker->unique()->lexify('????'),
            'anexo' => $this->faker->word,
            'departamento_id' => $random_departamento->id ?? Departamento::factory(),
        ];
    }
}
