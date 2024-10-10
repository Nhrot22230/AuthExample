<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Administrativo>
 */
class AdministrativoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $random_usuario = Usuario::inRandomOrder()->first();
        $lugaresTrabajo = ['Secretaría', 'Decanato', 'Dirección', 'Coordinación', 'Jefatura'];
        $cargos = ['Secretario', 'Decano', 'Director', 'Coordinador', 'Jefe'];

        return [
            'usuario_id' => $random_usuario->id ?? Usuario::factory(),
            'codigoAdministrativo' => $this->faker->unique()->randomNumber(8),
            'lugarTrabajo' => $this->faker->randomElement($lugaresTrabajo),
            'cargo' => $this->faker->randomElement($cargos),
        ];
    }
}
