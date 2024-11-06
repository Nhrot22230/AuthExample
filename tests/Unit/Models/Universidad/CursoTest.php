<?php

namespace Tests\Unit\Models\Universidad;

use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\PlanEstudio;
use App\Models\Requisito;
use App\Models\Horario;
use App\Models\EstudianteRiesgo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_curso_pertenece_a_una_especialidad()
    {
        $especialidad = Especialidad::factory()->create();
        $curso = Curso::factory()->create(['especialidad_id' => $especialidad->id]);

        $this->assertTrue($curso->especialidad->is($especialidad));
    }

    #[Test]
    public function test_curso_tiene_muchos_planes_estudio()
    {
        $curso = Curso::factory()->create();
        $planEstudios = PlanEstudio::factory()->count(3)->create();
        $curso->planesEstudio()->attach($planEstudios);

        $this->assertCount(3, $curso->planesEstudio);
    }

    #[Test]
    public function test_curso_tiene_muchos_requisitos()
    {
        $curso = Curso::factory()->create();
        Requisito::factory()->count(3)->create(['curso_id' => $curso->id]);

        $this->assertCount(3, $curso->requisitos);
    }

    #[Test]
    public function test_curso_tiene_muchos_horarios()
    {
        $curso = Curso::factory()->create();
        Horario::factory()->count(3)->create(['curso_id' => $curso->id]);

        $this->assertCount(3, $curso->horarios);
    }

    #[Test]
    public function test_curso_tiene_muchos_estudiantes_en_riesgo()
    {
        $this->markTestSkipped('Test omitido temporalmente mientras se resuelven dependencias.');

        $curso = Curso::factory()->create();
        EstudianteRiesgo::factory()->count(3)->create(['codigo_curso' => $curso->id]);

        $this->assertCount(3, $curso->estudiantesRiesgo);
    }
}
