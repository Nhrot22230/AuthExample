<?php

namespace Tests\Unit\Models\Universidad;

use App\Models\Facultad;
use App\Models\Departamento;
use App\Models\Especialidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FacultadTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_facultad_tiene_muchos_departamentos()
    {
        $facultad = Facultad::factory()->create();
        Departamento::factory()->count(3)->create(['facultad_id' => $facultad->id]);

        $this->assertCount(3, $facultad->departamentos);
    }

    #[Test]
    public function test_facultad_tiene_muchas_especialidades()
    {
        $facultad = Facultad::factory()->create();
        Especialidad::factory()->count(3)->create(['facultad_id' => $facultad->id]);

        $this->assertCount(3, $facultad->especialidades);
    }
}
