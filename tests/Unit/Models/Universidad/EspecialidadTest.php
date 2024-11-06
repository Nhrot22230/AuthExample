<?php

namespace Tests\Unit\Models\Universidad;

use App\Models\Encuestas\Encuesta;
use App\Models\EstudianteRiesgo\EstudianteRiesgo;
use App\Models\Universidad\Area;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Facultad;
use App\Models\Usuarios\Estudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EspecialidadTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_especialidad_pertenece_a_una_facultad()
    {
        $facultad = Facultad::factory()->create();
        $especialidad = Especialidad::factory()->create(['facultad_id' => $facultad->id]);

        $this->assertTrue($especialidad->facultad->is($facultad));
    }

    #[Test]
    public function test_especialidad_tiene_muchos_estudiantes()
    {
        $especialidad = Especialidad::factory()->create();
        Estudiante::factory()->count(3)->create(['especialidad_id' => $especialidad->id]);

        $this->assertCount(3, $especialidad->estudiantes);
    }

    #[Test]
    public function test_especialidad_tiene_muchos_cursos()
    {
        $especialidad = Especialidad::factory()->create();
        Curso::factory()->count(3)->create(['especialidad_id' => $especialidad->id]);

        $this->assertCount(3, $especialidad->cursos);
    }

    #[Test]
    public function test_especialidad_tiene_muchas_areas()
    {
        $especialidad = Especialidad::factory()->create();
        Area::factory()->count(3)->create(['especialidad_id' => $especialidad->id]);

        $this->assertCount(3, $especialidad->areas);
    }

    #[Test]
    public function test_especialidad_tiene_muchos_estudiantes_en_riesgo()
    {
        $this->markTestSkipped('Test omitido temporalmente mientras se resuelven dependencias.');

        $especialidad = Especialidad::factory()->create();
        EstudianteRiesgo::factory()->count(3)->create(['codigo_especialidad' => $especialidad->id]);

        $this->assertCount(3, $especialidad->estudiantesRiesgo);
    }

    #[Test]
    public function test_especialidad_tiene_muchas_encuestas()
    {
        $especialidad = Especialidad::factory()->create();
        Encuesta::factory()->count(3)->create(['especialidad_id' => $especialidad->id]);

        $this->assertCount(3, $especialidad->encuestas);
    }
}
