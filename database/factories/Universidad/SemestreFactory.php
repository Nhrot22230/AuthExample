<?php

namespace Database\Factories\Universidad;

use App\Models\Universidad\Semestre;
use Illuminate\Database\Eloquent\Factories\Factory;

class SemestreFactory extends Factory
{
    protected $model = Semestre::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $combinations = [];

        do {
            $anho = $this->faker->year;
            $periodo = $this->faker->randomElement(['0', '1', '2']);
            $uniqueKey = "$anho-$periodo";
        } while (in_array($uniqueKey, $combinations));

        $combinations[] = $uniqueKey;

        return [
            'anho' => $anho,
            'periodo' => $periodo,
            'fecha_inicio' => $this->faker->date(),
            'fecha_fin' => $this->faker->date(),
            'estado' => $this->faker->randomElement(['activo', 'inactivo']),
        ];
    }
}
