<?php

namespace Database\Factories\Authorization;

use App\Models\Authorization\Permission;
use App\Models\Authorization\PermissionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'guard_name' => 'api',
            'permission_category_id' => PermissionCategory::factory(),
        ];
    }
}
