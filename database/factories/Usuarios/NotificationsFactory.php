<?php

namespace Database\Factories\Usuarios;

use App\Models\Usuarios\Notifications;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notifications>
 */
class NotificationsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notifications::class;

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
            'usuario_id' => Usuario::factory(),
            'status' => false,
        ];
    }
}
