<?php

namespace Tests\Unit\Models\Universidad;

use App\Models\Horario;
use App\Models\Curso;
use App\Models\Semestre;
use App\Models\Docente;
use App\Models\JefePractica;
use App\Models\Encuesta;
use App\Models\HorarioEstudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HorarioTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_horario_pertenece_a_un_curso()
    {
        $curso = Curso::factory()->create();
        $horario = Horario::factory()->create(['curso_id' => $curso->id]);

        $this->assertTrue($horario->curso->is($curso));
    }

    #[Test]
    public function test_horario_pertenece_a_un_semestre()
    {
        $semestre = Semestre::factory()->create();
        $horario = Horario::factory()->create(['semestre_id' => $semestre->id]);

        $this->assertTrue($horario->semestre->is($semestre));
    }

    #[Test]
    public function test_horario_tiene_muchos_jefes_practicas()
    {
        $this->markTestSkipped('Test omitido temporalmente mientras se resuelven dependencias.');

        $horario = Horario::factory()->create();
        JefePractica::factory()->count(3)->create(['horario_id' => $horario->id]);

        $this->assertCount(3, $horario->jefePracticas);
    }

    #[Test]
    public function test_horario_tiene_muchos_docentes()
    {
        $horario = Horario::factory()->create();
        $docentes = Docente::factory()->count(3)->create();
        $horario->docentes()->attach($docentes);

        $this->assertCount(3, $horario->docentes);
    }

    #[Test]
    public function test_horario_tiene_muchos_estudiantes_a_traves_de_horario_estudiantes()
    {
        $horario = Horario::factory()->create();
        HorarioEstudiante::factory()->count(3)->create(['horario_id' => $horario->id]);

        $this->assertCount(3, $horario->estudiantes);
    }

    #[Test]
    public function test_horario_tiene_muchas_encuestas()
    {
        $horario = Horario::factory()->create();
        $encuestas = Encuesta::factory()->count(3)->create();
        $horario->encuestas()->attach($encuestas);

        $this->assertCount(3, $horario->encuestas);
    }
}
