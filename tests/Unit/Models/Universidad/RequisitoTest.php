<?php

namespace Tests\Unit\Models\Universidad;

use App\Models\Requisito;
use App\Models\Curso;
use App\Models\PlanEstudio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RequisitoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_requisito_pertenece_a_un_curso()
    {
        $curso = Curso::factory()->create();
        $requisito = Requisito::factory()->create(['curso_id' => $curso->id]);

        $this->assertTrue($requisito->curso->is($curso));
    }

    #[Test]
    public function test_requisito_pertenece_a_un_curso_requisito()
    {
        $curso = Curso::factory()->create();
        $cursoRequisito = Curso::factory()->create();
        $requisito = Requisito::factory()->create(['curso_id' => $curso->id, 'curso_requisito_id' => $cursoRequisito->id]);

        $this->assertTrue($requisito->cursoRequisito->is($cursoRequisito));
    }

    #[Test]
    public function test_requisito_pertenece_a_un_plan_estudio()
    {
        $planEstudio = PlanEstudio::factory()->create();
        $requisito = Requisito::factory()->create(['plan_estudio_id' => $planEstudio->id]);

        $this->assertTrue($requisito->planEstudio->is($planEstudio));
    }
}
