<?php

namespace Tests\Unit\Models\Usuarios;

use App\Models\Administrativo;
use App\Models\Usuario;
use App\Models\Facultad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdministrativoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function administrativo_pertenece_a_un_usuario()
    {
        $usuario = Usuario::factory()->create();
        $administrativo = Administrativo::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertTrue($administrativo->usuario->is($usuario));
    }

    #[Test]
    public function administrativo_pertenece_a_una_facultad()
    {
        $facultad = Facultad::factory()->create();
        $administrativo = Administrativo::factory()->create(['facultad_id' => $facultad->id]);

        $this->assertTrue($administrativo->facultad->is($facultad));
    }
}
