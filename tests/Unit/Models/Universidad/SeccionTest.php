<?php

namespace Tests\Unit\Models\Universidad;

use App\Models\Seccion;
use App\Models\Departamento;
use App\Models\Docente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeccionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_seccion_pertenece_a_un_departamento()
    {
        $departamento = Departamento::factory()->create();
        $seccion = Seccion::factory()->create(['departamento_id' => $departamento->id]);

        $this->assertTrue($seccion->departamento->is($departamento));
    }

    #[Test]
    public function test_seccion_tiene_muchos_docentes()
    {
        $seccion = Seccion::factory()->create();
        Docente::factory()->count(3)->create(['seccion_id' => $seccion->id]);

        $this->assertCount(3, $seccion->docentes);
    }
}
