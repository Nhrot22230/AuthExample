<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PreguntaFactory extends Factory
{

    public function definition(): array
    {
        return [
            'tipo_respuesta' => $this->faker->randomElement(['likert', 'porcentaje', 'texto']),
            'texto_pregunta' => $this->faker->sentence(),
        ];
    }
}
