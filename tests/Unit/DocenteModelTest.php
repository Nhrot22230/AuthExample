<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Docente;
use App\Models\Usuario;
use App\Models\Especialidad;
use App\Models\Area;
use App\Models\Seccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DocenteModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_un_docente()
    {
        $usuario = Usuario::factory()->create();
        $especialidad = Especialidad::factory()->create();
        $area = Area::factory()->create();
        $seccion = Seccion::factory()->create();

        $docente = Docente::create([
            'usuario_id' => $usuario->id,
            'codigoDocente' => 'DOC12345',
            'tipo' => 'TPA',
            'especialidad_id' => $especialidad->id,
            'seccion_id' => $seccion->id,
            'area_id' => $area->id,
        ]);

        $this->assertDatabaseHas('docentes', [
            'codigoDocente' => 'DOC12345',
            'usuario_id' => $usuario->id,
        ]);
    }

    #[Test]
    public function un_docente_tiene_un_usuario()
    {
        $usuario = Usuario::factory()->create();
        $docente = Docente::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        $this->assertInstanceOf(Usuario::class, $docente->usuario);
        $this->assertEquals($usuario->id, $docente->usuario->id);
    }

    #[Test]
    public function un_docente_tiene_una_especialidad()
    {
        $especialidad = Especialidad::factory()->create();
        $docente = Docente::factory()->create([
            'especialidad_id' => $especialidad->id,
        ]);

        $this->assertInstanceOf(Especialidad::class, $docente->especialidad);
    }

    #[Test]
    public function un_docente_tiene_un_area()
    {
        $area = Area::factory()->create();
        $docente = Docente::factory()->create([
            'area_id' => $area->id,
        ]);

        $this->assertInstanceOf(Area::class, $docente->area);
    }

    #[Test]
    public function un_docente_tiene_una_seccion()
    {
        $seccion = Seccion::factory()->create();
        $docente = Docente::factory()->create([
            'seccion_id' => $seccion->id,
        ]);

        $this->assertInstanceOf(Seccion::class, $docente->seccion);
    }
}
