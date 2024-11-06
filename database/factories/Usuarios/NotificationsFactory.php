<?php

namespace Database\Factories;

use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuarios\Notifications>
 */
class NotificationsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Usuarios\Notifications::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'message' => $this->faker->paragraph,
            'message_type' => $this->faker->randomElement(['info', 'warning', 'error', 'success']),
            'usuario_id' => Usuario::inRandomOrder()->first() ?? Usuario::factory(),
            'status' => false,
        ];
    }
}
