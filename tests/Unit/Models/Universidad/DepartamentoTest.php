<?php

namespace Tests\Unit\Models\Universidad;

use App\Models\Departamento;
use App\Models\Facultad;
use App\Models\Seccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DepartamentoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_departamento_pertenece_a_una_facultad()
    {
        $facultad = Facultad::factory()->create();
        $departamento = Departamento::factory()->create(['facultad_id' => $facultad->id]);

        $this->assertTrue($departamento->facultad->is($facultad));
    }

    #[Test]
    public function test_departamento_tiene_muchas_secciones()
    {
        $departamento = Departamento::factory()->create();
        Seccion::factory()->count(3)->create(['departamento_id' => $departamento->id]);

        $this->assertCount(3, $departamento->secciones);
    }
}
