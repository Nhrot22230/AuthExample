<?php

namespace Tests\Unit\Models\Usuarios;

use App\Models\Estudiante;
use App\Models\Usuario;
use App\Models\Especialidad;
use App\Models\HorarioEstudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstudianteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function estudiante_pertenece_a_un_usuario()
    {
        $usuario = Usuario::factory()->create();
        $estudiante = Estudiante::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertTrue($estudiante->usuario->is($usuario));
    }

    #[Test]
    public function estudiante_pertenece_a_una_especialidad()
    {
        $especialidad = Especialidad::factory()->create();
        $estudiante = Estudiante::factory()->create(['especialidad_id' => $especialidad->id]);

        $this->assertTrue($estudiante->especialidad->is($especialidad));
    }

    #[Test]
    public function estudiante_tiene_muchos_horarios_a_traves_de_horario_estudiantes()
    {
        $estudiante = Estudiante::factory()->create();
        HorarioEstudiante::factory()->count(2)->create(['estudiante_id' => $estudiante->id]);

        $this->assertCount(2, $estudiante->horarios);
    }
}
