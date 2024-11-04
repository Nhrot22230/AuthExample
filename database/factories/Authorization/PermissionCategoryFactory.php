<?php

namespace Database\Factories\Authorization;

use App\AccessPath;
use App\Models\Authorization\PermissionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Authorization\PermissionCategory>
 */
class PermissionCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PermissionCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'access_path' => AccessPath::random(),
        ];
    }
}
