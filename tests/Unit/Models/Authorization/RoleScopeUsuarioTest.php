<?php

namespace Tests\Unit\Models\Authorization;

use App\Models\Authorization\Role;
use App\Models\Authorization\Scope;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Usuario;
use App\Models\Area;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Especialidad;
use App\Models\Facultad;
use App\Models\Seccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Test;
use Tests\TestCase;

class RoleScopeUsuarioTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_role_scope_usuario_belongs_to_role()
    {
        $role = Role::factory()->create();
        $roleScopeUsuario = RoleScopeUsuario::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($roleScopeUsuario->role->is($role));
    }

    #[Test]
    public function test_role_scope_usuario_belongs_to_scope()
    {
        $scope = Scope::factory()->create();
        $roleScopeUsuario = RoleScopeUsuario::factory()->create(['scope_id' => $scope->id]);

        $this->assertTrue($roleScopeUsuario->scope->is($scope));
    }

    #[Test]
    public function test_role_scope_usuario_belongs_to_usuario()
    {
        $usuario = Usuario::factory()->create();
        $roleScopeUsuario = RoleScopeUsuario::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertTrue($roleScopeUsuario->usuario->is($usuario));
    }

    #[Test]
    public function test_role_scope_usuario_cannot_be_created_without_entity_id_and_entity_type()
    {
        $this->expectException(\Exception::class);
        RoleScopeUsuario::factory()->create(['entity_id' => null, 'entity_type' => null]);
    }

    public function test_role_scope_usuario_has_morph_to_entity_with_different_models()
    {
        $departamento = Departamento::factory()->create();
        $roleScopeUsuario = RoleScopeUsuario::factory()->create([
            'entity_id' => $departamento->id,
            'entity_type' => Departamento::class,
        ]);

        $this->assertTrue($roleScopeUsuario->entity->is($departamento));
        $this->assertEquals(Departamento::class, $roleScopeUsuario->entity_type);

        $curso = Curso::factory()->create();
        $roleScopeUsuario = RoleScopeUsuario::factory()->create([
            'entity_id' => $curso->id,
            'entity_type' => Curso::class,
        ]);

        $this->assertTrue($roleScopeUsuario->entity->is($curso));
        $this->assertEquals(Curso::class, $roleScopeUsuario->entity_type);

        $area = Area::factory()->create();
        $roleScopeUsuario = RoleScopeUsuario::factory()->create([
            'entity_id' => $area->id,
            'entity_type' => Area::class,
        ]);

        $this->assertTrue($roleScopeUsuario->entity->is($area));
        $this->assertEquals(Area::class, $roleScopeUsuario->entity_type);

        $facultad = Facultad::factory()->create();
        $roleScopeUsuario = RoleScopeUsuario::factory()->create([
            'entity_id' => $facultad->id,
            'entity_type' => Facultad::class,
        ]);

        $this->assertTrue($roleScopeUsuario->entity->is($facultad));
        $this->assertEquals(Facultad::class, $roleScopeUsuario->entity_type);

        $seccion = Seccion::factory()->create();
        $roleScopeUsuario = RoleScopeUsuario::factory()->create([
            'entity_id' => $seccion->id,
            'entity_type' => Seccion::class,
        ]);

        $this->assertTrue($roleScopeUsuario->entity->is($seccion));
        $this->assertEquals(Seccion::class, $roleScopeUsuario->entity_type);

        $especialidad = Especialidad::factory()->create();
        $roleScopeUsuario = RoleScopeUsuario::factory()->create([
            'entity_id' => $especialidad->id,
            'entity_type' => Especialidad::class,
        ]);

        $this->assertTrue($roleScopeUsuario->entity->is($especialidad));
        $this->assertEquals(Especialidad::class, $roleScopeUsuario->entity_type);
    }
}