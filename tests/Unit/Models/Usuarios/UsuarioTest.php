<?php

namespace Tests\Unit\Models\Usuarios;

use App\Models\Usuarios\Administrativo;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function usuario_tiene_una_relacion_con_docente()
    {
        $usuario = Usuario::factory()->create();
        $docente = Docente::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertTrue($usuario->docente->is($docente));
    }

    #[Test]
    public function usuario_tiene_una_relacion_con_estudiante()
    {
        $usuario = Usuario::factory()->create();
        $estudiante = Estudiante::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertTrue($usuario->estudiante->is($estudiante));
    }

    #[Test]
    public function usuario_tiene_una_relacion_con_administrativo()
    {
        $usuario = Usuario::factory()->create();
        $administrativo = Administrativo::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertTrue($usuario->administrativo->is($administrativo));
    }
}
