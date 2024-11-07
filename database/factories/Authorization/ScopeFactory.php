<?php

namespace Database\Factories\Authorization;

use App\Models\Authorization\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scope>
 */
class ScopeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Scope::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'entity_type' => $this->faker->word,
        ];
    }
}
