<?php

namespace Database\Factories;

use App\Models\Universidad\Facultad;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuarios\Administrativo>
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
        $lugaresTrabajo = ['Secretaría', 'Decanato', 'Dirección', 'Coordinación', 'Jefatura'];
        $cargos = ['Secretario', 'Decano', 'Director', 'Coordinador', 'Jefe'];

        $random_facultad = Facultad::inRandomOrder()->first();

        return [
            'usuario_id' => Usuario::factory(),
            'codigoAdministrativo' => $this->faker->unique()->randomNumber(8),
            'lugarTrabajo' => $this->faker->randomElement($lugaresTrabajo),
            'cargo' => $this->faker->randomElement($cargos),
            'facultad_id' => $random_facultad->id ?? Facultad::factory(),
        ];
    }
}