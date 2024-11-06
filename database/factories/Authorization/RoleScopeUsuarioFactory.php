<?php

namespace Database\Factories\Authorization;

use App\Models\Area;
use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Especialidad;
use App\Models\Facultad;
use App\Models\Seccion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Authorization\RoleScopeUsuario>
 */
class RoleScopeUsuarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        do {
            $role = Role::factory()->create();
            $scope = Scope::factory()->create();
            $usuario = Usuario::factory()->create();

            $entityType = $this->faker->randomElement([
                Departamento::class,
                Curso::class,
                Area::class,
                Facultad::class,
                Especialidad::class,
                Seccion::class,
            ]);

            $entity = $entityType::factory()->create();

            $exists = RoleScopeUsuario::where('role_id', $role->id)
                ->where('scope_id', $scope->id)
                ->where('usuario_id', $usuario->id)
                ->where('entity_id', $entity->id)
                ->where('entity_type', $entityType)
                ->exists();
        } while ($exists);

        return [
            'role_id' => Role::factory(),
            'scope_id' => Scope::factory(),
            'usuario_id' => Usuario::factory(),
            'entity_type' => $entityType,
            'entity_id' => $entity->id,
        ];
    }
}
