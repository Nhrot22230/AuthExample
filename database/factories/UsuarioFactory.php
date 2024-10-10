<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    // Hashea la contraseña solo una vez
    protected static $hashedPassword;

    public function definition(): array
    {
        if (!self::$hashedPassword) {
            self::$hashedPassword = Hash::make('password');
        }

        return [
            'nombre' => $this->faker->firstName(),
            'apellido_paterno' => $this->faker->lastName(),
            'apellido_materno' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => self::$hashedPassword,
            'estado' => $this->faker->randomElement(['activo', 'inactivo']), 
        ];
    }
}
