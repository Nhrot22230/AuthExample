<?php

namespace Database\Factories\Usuarios;

use App\Models\Universidad\Facultad;
use App\Models\Usuarios\Administrativo;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Administrativo>
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

        return [
            'usuario_id' => Usuario::factory(),
            'codigoAdministrativo' => $this->faker->unique()->randomNumber(8),
            'lugarTrabajo' => $this->faker->randomElement($lugaresTrabajo),
            'cargo' => $this->faker->randomElement($cargos),
            'facultad_id' => Facultad::factory(),
        ];
    }
}
