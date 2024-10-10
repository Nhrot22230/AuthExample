<?php

namespace Tests\Unit;

use App\Http\Controllers\Usuarios\UsuarioController;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Administrativo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class UsuarioModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function un_usuario_puede_ser_un_docente()
    {
        $usuario = Usuario::factory()->create();
        $docente = Docente::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertInstanceOf(Docente::class, $usuario->docente);
        $this->assertEquals($docente->id, $usuario->docente->id);
    }

    #[Test]
    public function un_usuario_puede_ser_un_estudiante()
    {
        $usuario = Usuario::factory()->create();
        $estudiante = Estudiante::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertInstanceOf(Estudiante::class, $usuario->estudiante);
        $this->assertEquals($estudiante->id, $usuario->estudiante->id);
    }

    #[Test]
    public function un_usuario_puede_ser_un_administrativo()
    {
        $usuario = Usuario::factory()->create();
        $administrativo = Administrativo::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertInstanceOf(Administrativo::class, $usuario->administrativo);
        $this->assertEquals($administrativo->id, $usuario->administrativo->id);
    }

    #[Test]
    public function puede_listar_los_usuarios_directamente_en_el_controlador()
    {
        Usuario::factory()->count(10)->create();

        $controller = new UsuarioController();
        $response = $controller->index();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(10, $response->getData()->usuarios->data);
    }

    #[Test]
    public function puede_crear_un_usuario_directamente_en_el_controlador()
    {
        $controller = new UsuarioController();

        $request = Request::create('/api/v1/usuarios', 'POST', [
            'nombre' => 'Juan',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Lopez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'google_id' => '123456789',
            'picture' => 'https://example.com/picture.jpg',
        ]);

        $response = $controller->store($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertDatabaseHas('usuarios', [
            'email' => 'juan@example.com',
            'google_id' => '123456789',
        ]);
    }

    #[Test]
    public function puede_actualizar_un_usuario_directamente_en_el_controlador()
    {
        $usuario = Usuario::factory()->create([
            'nombre' => 'Juan',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Lopez',
            'email' => 'juan@example.com',
        ]);

        $controller = new UsuarioController();

        $request = Request::create('/api/v1/usuarios/' . $usuario->id, 'PUT', [
            'nombre' => 'Juan Actualizado',
            'apellido_paterno' => 'Perez Actualizado',
            'apellido_materno' => 'Lopez Actualizado',
            'email' => 'juan.actualizado@example.com',
        ]);

        $response = $controller->update($request, $usuario->id);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseHas('usuarios', [
            'id' => $usuario->id,
            'nombre' => 'Juan Actualizado',
            'apellido_paterno' => 'Perez Actualizado',
            'apellido_materno' => 'Lopez Actualizado',
            'email' => 'juan.actualizado@example.com',
        ]);
    }

    #[Test]
    public function puede_eliminar_un_usuario_directamente_en_el_controlador()
    {
        $usuario = Usuario::factory()->create();
        $controller = new UsuarioController();
        $response = $controller->destroy($usuario->id);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertDatabaseMissing('usuarios', [
            'id' => $usuario->id,
        ]);
    }
}
