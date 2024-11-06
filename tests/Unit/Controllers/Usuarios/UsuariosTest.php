<?php

namespace Tests\Unit\Controllers\Usuarios;

use App\Models\Usuarios\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuariosTest extends TestCase
{
    use RefreshDatabase;

    private $base_url = '/api/v1/usuarios';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    #[Test]
    public function it_can_list_users()
    {
        Usuario::factory()->count(5)->create();

        $response = $this->getJson($this->base_url);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'links']);
    }

    #[Test]
    public function it_can_show_a_single_user()
    {
        $usuario = Usuario::factory()->create();

        $response = $this->getJson($this->base_url . "/{$usuario->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $usuario->id,
                     'nombre' => $usuario->nombre,
                 ]);
    }

    #[Test]
    public function it_returns_404_if_user_not_found()
    {
        $response = $this->getJson($this->base_url . '/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Usuario no encontrado']);
    }

    #[Test]
    public function it_can_create_a_new_user()
    {
        $userData = [
            'nombre' => 'Juan',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Lopez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'estado' => 'activo',
        ];

        $response = $this->postJson($this->base_url, $userData);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Usuario creado exitosamente',
                     'usuario' => [
                         'nombre' => 'Juan',
                         'email' => 'juan@example.com',
                     ],
                 ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'juan@example.com',
        ]);
    }

    #[Test]
    public function it_requires_unique_email_when_creating_user()
    {
        Usuario::factory()->create(['email' => 'test@example.com']);

        $userData = [
            'nombre' => 'Pedro',
            'apellido_paterno' => 'Gomez',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson($this->base_url, $userData);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_can_update_a_user()
    {
        $usuario = Usuario::factory()->create();

        $updatedData = [
            'nombre' => 'Carlos',
            'apellido_paterno' => 'Martinez',
            'email' => $usuario->email,
        ];

        $response = $this->putJson($this->base_url . "/{$usuario->id}", $updatedData);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Usuario actualizado exitosamente',
                     'usuario' => [
                         'nombre' => 'Carlos',
                         'apellido_paterno' => 'Martinez',
                     ],
                 ]);

        $this->assertDatabaseHas('usuarios', [
            'id' => $usuario->id,
            'nombre' => 'Carlos',
        ]);
    }

    #[Test]
    public function it_can_delete_a_user()
    {
        $usuario = Usuario::factory()->create();

        $response = $this->deleteJson($this->base_url . "/{$usuario->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Usuario eliminado exitosamente']);

        $this->assertDatabaseMissing('usuarios', [
            'id' => $usuario->id,
        ]);
    }

    #[Test]
    public function it_returns_404_if_user_to_update_is_not_found()
    {
        $response = $this->putJson($this->base_url . '/999', [
            'nombre' => 'Carlos',
            'apellido_paterno' => 'Martinez',
            'email' => 'carlos@example.com',
        ]);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Usuario no encontrado']);
    }

    #[Test]
    public function it_returns_404_if_user_to_delete_is_not_found()
    {
        $response = $this->deleteJson($this->base_url . '/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Usuario no encontrado']);
    }
}
