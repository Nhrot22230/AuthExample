<?php

namespace Tests\Unit\Models\Universidad;

use App\Models\Universidad\Area;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Docente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AreaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_area_pertenece_a_una_especialidad()
    {
        $especialidad = Especialidad::factory()->create();
        $area = Area::factory()->create(['especialidad_id' => $especialidad->id]);

        $this->assertTrue($area->especialidad->is($especialidad));
    }

    #[Test]
    public function test_area_tiene_muchos_docentes()
    {
        $area = Area::factory()->create();
        Docente::factory()->count(3)->create(['area_id' => $area->id]);

        $this->assertCount(3, $area->docentes);
    }
}
