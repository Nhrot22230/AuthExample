<?php

namespace Database\Factories\Universidad;

use App\Models\Universidad\Departamento;
use App\Models\Universidad\Seccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seccion>
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
        $nombre_seccion = $this->faker->randomElement(['Sección', 'Grupo', 'Clase']) . ' ' .
            $this->faker->randomElement(['A', 'B', 'C', 'D', '1', '2', '3', '4']);

        return [
            'nombre' => $nombre_seccion,
            'departamento_id' => Departamento::factory(),
        ];
    }
}
