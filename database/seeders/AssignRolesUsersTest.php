<?php

namespace Database\Seeders;

use App\Models\Usuarios\Usuario;
use Illuminate\Database\Seeder;

class AssignRolesUsersTest extends Seeder
{
    public function run(): void
    {
        Usuario::find(1)->assignRole('administrador');
        Usuario::find(2)->assignRole('secretario-academico');
        Usuario::find(3)->assignRole('asistente-especialidad');
        Usuario::find(4)->assignRole('asistente-seccion');
        Usuario::find(6)->assignRole('coordinador-area');
        Usuario::find(7)->assignRole('coordinador-seccion');
        Usuario::find(8)->assignRole('docente');
        Usuario::find(9)->assignRole('director');
        Usuario::find(10)->assignRole('estudiante');
    }
}
