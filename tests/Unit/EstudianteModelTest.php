<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Especialidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class EstudianteModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_un_estudiante()
    {
        $usuario = Usuario::factory()->create();
        $especialidad = Especialidad::factory()->create();

        $estudiante = Estudiante::create([
            'usuario_id' => $usuario->id,
            'codigoEstudiante' => 'EST12345',
            'especialidad_id' => $especialidad->id,
        ]);

        $this->assertDatabaseHas('estudiantes', [
            'codigoEstudiante' => 'EST12345',
            'usuario_id' => $usuario->id,
        ]);
    }

    #[Test]
    public function un_estudiante_tiene_un_usuario()
    {
        $usuario = Usuario::factory()->create();
        $especialidad = Especialidad::factory()->create();

        $estudiante = Estudiante::create([
            'usuario_id' => $usuario->id,
            'codigoEstudiante' => 'EST12345',
            'especialidad_id' => $especialidad->id,
        ]);

        $this->assertInstanceOf(Usuario::class, $estudiante->usuario);
        $this->assertEquals($usuario->id, $estudiante->usuario->id);
    }

    #[Test]
    public function un_estudiante_tiene_una_especialidad()
    {
        $especialidad = Especialidad::factory()->create();
        $estudiante = Estudiante::factory()->create([
            'especialidad_id' => $especialidad->id,
        ]);

        $this->assertInstanceOf(Especialidad::class, $estudiante->especialidad);
        $this->assertEquals($especialidad->id, $estudiante->especialidad->id);
    }
}
