<?php

namespace Tests\Unit\Models\Usuarios;

use App\Models\Docente;
use App\Models\Usuario;
use App\Models\Especialidad;
use App\Models\Seccion;
use App\Models\Area;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DocenteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function docente_pertenece_a_un_usuario()
    {
        $usuario = Usuario::factory()->create();
        $docente = Docente::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertTrue($docente->usuario->is($usuario));
    }

    #[Test]
    public function docente_pertenece_a_una_especialidad()
    {
        $especialidad = Especialidad::factory()->create();
        $docente = Docente::factory()->create(['especialidad_id' => $especialidad->id]);

        $this->assertTrue($docente->especialidad->is($especialidad));
    }

    #[Test]
    public function docente_pertenece_a_un_area()
    {
        $area = Area::factory()->create();
        $docente = Docente::factory()->create(['area_id' => $area->id]);

        $this->assertTrue($docente->area->is($area));
    }

    #[Test]
    public function docente_pertenece_a_una_seccion()
    {
        $seccion = Seccion::factory()->create();
        $docente = Docente::factory()->create(['seccion_id' => $seccion->id]);

        $this->assertTrue($docente->seccion->is($seccion));
    }
}