<?php

namespace Database\Factories\Usuarios;

use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Usuario::class;

    protected static string $hashedPassword = "";

    public function definition(): array
    {
        if (self::$hashedPassword == "") {
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
