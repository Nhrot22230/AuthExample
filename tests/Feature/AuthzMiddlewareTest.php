<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Authorization\Permission;
use App\Models\Authorization\Role;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Facultad;
use App\Models\Especialidad;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Seccion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthzMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'ver especialidades']);
        Role::firstOrCreate(['name' => 'Administrador']);
        Role::firstOrCreate(['name' => 'Docente'])->givePermissionTo('ver especialidades');
        Scope::create(['name' => 'departamento', 'entity_type' => Departamento::class]);
        Scope::create(['name' => 'facultad', 'entity_type' => Facultad::class]);
        Scope::create(['name' => 'especialidad', 'entity_type' => Especialidad::class]);
    }
    
    #[Test]
    public function it_allows_direct_access_to_especialidad()
    {
        $especialidad = Especialidad::factory()->create();
        $user = Usuario::factory()->create([
            'password' => Hash::make('password123')
        ]);
        
        $role = Role::findByName('Docente');
        $scope = Scope::where('name', 'especialidad')->first();
        
        $role->scopes()->attach($scope);
        $user->assignRole($role);
        
        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => $scope->id,
            'usuario_id' => $user->id,
            'entity_id' => $especialidad->id,
            'entity_type' => Especialidad::class
        ]);

        $this->actingAs($user)
            ->get("/api/v1/especialidades/{$especialidad->id}")
            ->assertStatus(200);
    }

    #[Test]
    public function it_allows_access_to_especialidad_if_user_has_access_to_facultad()
    {
        $facultad = Facultad::factory()->create();
        $especialidad = Especialidad::factory()->create(['facultad_id' => $facultad->id]);
        $user = Usuario::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $role = Role::findByName('Docente');
        $user->assignRole($role);

        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::where('name', 'facultad')->first()->id,
            'usuario_id' => $user->id,
            'entity_id' => $facultad->id,
            'entity_type' => Facultad::class
        ]);

        $this->actingAs($user)
            ->get("/api/v1/especialidades/{$especialidad->id}")
            ->assertStatus(200);
    }

    #[Test]
    public function it_denies_access_if_user_lacks_permissions()
    {
        $especialidad = Especialidad::factory()->create();
        $user = Usuario::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $this->actingAs($user)
            ->get("/api/v1/especialidades/{$especialidad->id}")
            ->assertStatus(403);
    }

    #[Test]
    public function it_allows_access_for_admin_role()
    {
        $especialidad = Especialidad::factory()->create();
        $user = Usuario::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $adminRole = Role::where('name', 'Administrador')->first();
        $user->assignRole($adminRole);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $token = $response->json('access_token');
        assert($token !== null);

        $this->withHeaders(['Authorization' => "Bearer $token"])
            ->get("/api/v1/especialidades/{$especialidad->id}")
            ->assertStatus(200);
    }


    #[Test]
    public function it_allows_access_to_all_courses_in_facultad()
    {
        $facultad = Facultad::factory()->create();
        $especialidad = Especialidad::factory()->create(['facultad_id' => $facultad->id]);
        $curso = Curso::factory()->create(['especialidad_id' => $especialidad->id]);

        $user = Usuario::factory()->create([
            'password' => Hash::make('password123')
        ]);
        $role = Role::where('name', 'Docente')->first();
        Permission::create(['name' => 'ver cursos']);
        $role->givePermissionTo('ver cursos');
        $user->assignRole($role);

        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::where('name', 'facultad')->first()->id,
            'usuario_id' => $user->id,
            'entity_id' => $facultad->id,
            'entity_type' => Facultad::class
        ]);

        assert(Curso::find($curso->id) !== null);

        $this->actingAs($user)
            ->get("/api/v1/cursos/{$curso->id}")
            ->assertStatus(200);
    }
    #[Test]
    public function it_allows_access_to_departamento_if_user_has_access_to_facultad()
    {
        $facultad = Facultad::factory()->create();
        $departamento = Departamento::factory()->create(['facultad_id' => $facultad->id]);
        $user = Usuario::factory()->create(['password' => Hash::make('password123')]);

        $role = Role::findByName('Docente');
        Permission::create(['name' => 'ver departamentos']);
        $role->givePermissionTo('ver departamentos');
        $user->assignRole($role);

        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::where('name', 'facultad')->first()->id,
            'usuario_id' => $user->id,
            'entity_id' => $facultad->id,
            'entity_type' => Facultad::class
        ]);

        $this->actingAs($user)
            ->get("/api/v1/departamentos/{$departamento->id}")
            ->assertStatus(200);
    }

    #[Test]
    public function it_allows_access_to_seccion_if_user_has_access_to_departamento()
    {
        $departamento = Departamento::factory()->create();
        $seccion = Seccion::factory()->create(['departamento_id' => $departamento->id]);
        $user = Usuario::factory()->create(['password' => Hash::make('password123')]);

        $role = Role::findByName('Docente');
        Permission::create(['name' => 'ver secciones']);
        $role->givePermissionTo('ver secciones');
        $user->assignRole($role);

        RoleScopeUsuario::create([
            'role_id' => $role->id,
            'scope_id' => Scope::where('name', 'departamento')->first()->id,
            'usuario_id' => $user->id,
            'entity_id' => $departamento->id,
            'entity_type' => Departamento::class
        ]);

        $this->actingAs($user)
            ->get("/api/v1/secciones/{$seccion->id}")
            ->assertStatus(200);
    }

    #[Test]
    public function it_denies_access_to_curso_if_user_lacks_access_to_facultad()
    {
        $facultad = Facultad::factory()->create();
        $especialidad = Especialidad::factory()->create(['facultad_id' => $facultad->id]);
        $curso = Curso::factory()->create(['especialidad_id' => $especialidad->id]);
        $user = Usuario::factory()->create(['password' => Hash::make('password123')]);

        $role = Role::findByName('Docente');
        Permission::create(['name' => 'ver cursos']);
        $role->givePermissionTo('ver cursos');
        $user->assignRole($role);

        $this->actingAs($user)
            ->get("/api/v1/cursos/{$curso->id}")
            ->assertStatus(403);
    }

    #[Test]
    public function it_denies_access_to_area_if_user_lacks_access_to_especialidad()
    {
        $especialidad = Especialidad::factory()->create();
        $area = Area::factory()->create(['especialidad_id' => $especialidad->id]);
        $user = Usuario::factory()->create(['password' => Hash::make('password123')]);

        $role = Role::findByName('Docente');
        Permission::create(['name' => 'ver áreas']);
        $role->givePermissionTo('ver áreas');
        $user->assignRole($role);

        $this->actingAs($user)
            ->get("/api/v1/areas/{$area->id}")
            ->assertStatus(403);
    }
}
