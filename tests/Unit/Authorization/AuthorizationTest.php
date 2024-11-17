<?php

namespace Tests\Unit;

use App\Models\Authorization\Permission;
use App\Models\Authorization\Role;
use App\Models\Authorization\Scope;
use App\Models\Usuarios\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->assignPermissionsToRoles();
    }
    protected function assignPermissionsToRoles()
    {
        // Permisos por rol
        $permissions = [
            'administrador' => Permission::all()->pluck('name')->toArray(),
            'secretario-academico' => ['facultades', 'departamentos', 'pedido-cursos'],
            'asistente' => Permission::all()->pluck('name')->toArray(),
            'director' => ['especialidades', 'cursos', 'secciones'],
            'docente' => ['cursos', 'secciones', 'areas'],
            'jefe-practica' => ['cursos'],
            'estudiante' => ['mis-cursos', 'mis-horarios'],
        ];

        foreach ($permissions as $roleName => $permissionNames) {
            $role = Role::findByName($roleName);
            $role->syncPermissions($permissionNames);
        }
    }

    #[Test]
    public function test_administrador_tiene_acceso_a_todos_los_permisos()
    {
        $admin = Usuario::factory()->create();
        $admin->assignRole('administrador');

        foreach (Permission::all() as $permission) {
            $this->assertTrue($admin->hasPermissionTo($permission->name), "Admin no tiene permiso para {$permission->name}");
        }
    }

    #[Test]
    public function solo_director_puede_acceder_a_especialidad_scope()
    {
        $director = Usuario::factory()->create();
        $director->assignRole('director');

        $this->assertTrue(
            $director->scopes()->contains('name', 'Especialidad'),
            'Director no tiene acceso al scope Especialidad'
        );

        $docente = Usuario::factory()->create();
        $docente->assignRole('docente');

        $this->assertFalse(
            $docente->scopes()->contains('name', 'Especialidad'),
            'Docente tiene acceso indebido al scope Especialidad'
        );
    }

    #[Test]
    public function secretario_academico_puede_acceder_a_facultad_scope()
    {
        $secretario = Usuario::factory()->create();
        $secretario->assignRole('secretario-academico');

        $this->assertTrue(
            $secretario->scopes()->contains('name', 'Facultad'),
            'Secretario académico no tiene acceso al scope Facultad'
        );
    }

    #[Test]
    public function asistente_tiene_acceso_a_todos_los_scopes()
    {
        $asistente = Usuario::factory()->create();
        $asistente->assignRole('asistente');
        
        $scopes = Scope::all()->pluck('name')->toArray();
        $asistente_scopes = $asistente->scopes()->pluck('name')->toArray();

        $this->assertEquals($scopes, $asistente_scopes, 'Asistente no tiene acceso a todos los scopes' );
    }

    #[Test]
    public function permiso_especifico_para_acceder_a_mis_unidades()
    {
        $user = Usuario::factory()->create();
        $user->givePermissionTo('mis-unidades');

        $this->assertTrue($user->can('mis-unidades'), 'Usuario no tiene permiso para mis-unidades');
    }

    #[Test]
    public function usuario_sin_permiso_no_puede_acceder_a_mis_encuestas()
    {
        $user = Usuario::factory()->create();

        $this->assertFalse($user->can('mis-encuestas'), 'Usuario sin permiso puede acceder a mis-encuestas');
    }

    #[Test]
    public function docente_tiene_acceso_a_cursos_y_areas()
    {
        $docente = Usuario::factory()->create();
        $docente->assignRole('docente');

        $this->assertTrue($docente->scopes()->contains('name', 'Curso'), 'Docente no tiene acceso al scope Curso');
        $this->assertTrue($docente->scopes()->contains('name', 'Area'), 'Docente no tiene acceso al scope Area');
    }

    #[Test]
    public function estudiante_no_tiene_acceso_a_matricula_adicional()
    {
        $estudiante = Usuario::factory()->create();
        $estudiante->assignRole('estudiante');

        $this->assertFalse($estudiante->can('matricula-adicional'), 'Estudiante tiene acceso indebido a matricula-adicional');
    }

    #[Test]
    public function jefe_practica_solo_acceso_a_cursos_scope()
    {
        $jefePractica = Usuario::factory()->create();
        $jefePractica->assignRole('jefe-practica');

        $this->assertTrue($jefePractica->scopes()->contains('name', 'Curso'), 'Jefe de práctica no tiene acceso al scope Curso');
        $this->assertFalse($jefePractica->scopes()->contains('name', 'Area'), 'Jefe de práctica tiene acceso indebido al scope Area');
    }

    #[Test]
    public function usuario_no_autorizado_no_puede_realizar_acciones_de_autorizacion()
    {
        $user = Usuario::factory()->create();

        $this->assertFalse($user->can('autorizacion'), 'Usuario no autorizado puede realizar acciones de autorizacion');
    }

    #[Test]
    public function usuario_puede_acceder_a_permisos_asignados_en_su_categoria()
    {
        $user = Usuario::factory()->create();
        $user->givePermissionTo('jurado-tesis');

        $this->assertTrue($user->can('jurado-tesis'), 'Usuario no tiene permiso para jurado-tesis');
    }

    #[Test]
    public function usuario_sin_permisos_no_puede_acceder_a_scopes_o_permisos()
    {
        $user = Usuario::factory()->create();
        $this->assertFalse($user->hasAnyPermission(Permission::all()), 'Usuario sin permisos puede acceder a permisos');
        $this->assertEmpty($user->scopes(), 'Usuario sin permisos tiene acceso a scopes');
    }
}
