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
        $semestre = Semestre::orderBy('anho', 'desc')->first();

        return [
            'anho' => $semestre ? $semestre->anho + 1 : $this->faker->year(),
            'periodo' => $this->faker->numberBetween(0, 2),
            'fecha_inicio' => $this->faker->date(),
            'fecha_fin' => $this->faker->date(),
            'estado' => 'inactivo',
        ];
    }
}
