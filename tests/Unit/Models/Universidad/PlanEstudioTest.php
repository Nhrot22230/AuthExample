<?php

namespace Tests\Unit\Models\Universidad;

use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Semestre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlanEstudioTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_plan_estudio_pertenece_a_una_especialidad()
    {
        $especialidad = Especialidad::factory()->create();
        $planEstudio = PlanEstudio::factory()->create(['especialidad_id' => $especialidad->id]);

        $this->assertTrue($planEstudio->especialidad->is($especialidad));
    }

    #[Test]
    public function test_plan_estudio_tiene_muchos_semestres()
    {
        $planEstudio = PlanEstudio::factory()->create();
        $semestres = Semestre::factory()->count(3)->create();
        $planEstudio->semestres()->attach($semestres);

        $this->assertCount(3, $planEstudio->semestres);
    }

    #[Test]
    public function test_plan_estudio_tiene_muchos_cursos_con_pivot()
    {
        $planEstudio = PlanEstudio::factory()->create();
        $curso = Curso::factory()->create();
        $planEstudio->cursos()->attach($curso, ['nivel' => '2', 'creditosReq' => 6]);

        $this->assertEquals('2', $planEstudio->cursos()->first()->pivot->nivel);
        $this->assertEquals(6, $planEstudio->cursos()->first()->pivot->creditosReq);
    }
}
