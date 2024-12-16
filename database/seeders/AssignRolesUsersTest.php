<?php

namespace Database\Seeders;

use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Universidad\Area;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Facultad;
use App\Models\Universidad\Seccion;
use App\Models\Usuarios\Administrativo;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Seeder;

class AssignRolesUsersTest extends Seeder
{
    public function run(): void
    {
        Usuario::find(1)->assignRole('administrador');
        Usuario::find(2)->assignRole('secretario-academico');
        RoleScopeUsuario::firstOrCreate([
            'role_id' => 2,
            'scope_id' => 2,
            'usuario_id' => 2,
            'entity_type' => Facultad::class,
            'entity_id' => 1,
        ]);
        if (
            Facultad::class === Facultad::class &&
            str_contains('secretario-academico', 'secret') &&
            Administrativo::whereHas('usuario', function ($query) {
                $query->where('id', 2);
            })->exists()
        ) {
            // Encontrar el administrativo asociado
            $administrativo = Administrativo::whereHas('usuario', function ($query) {
                $query->where('id', 2);
            })->first();

            // Modificar el facultad_id del administrativo
            $administrativo->update(['facultad_id' => 1]);
        }
        Usuario::find(3)->assignRole('asistente-especialidad');
        RoleScopeUsuario::firstOrCreate([
            'role_id' => 3,
            'scope_id' => 3,
            'usuario_id' => 3,
            'entity_type' => Especialidad::class,
            'entity_id' => 1,
        ]);
        Usuario::find(4)->assignRole('asistente-seccion');
        RoleScopeUsuario::firstOrCreate([
            'role_id' => 4,
            'scope_id' => 4,
            'usuario_id' => 4,
            'entity_type' => Seccion::class,
            'entity_id' => 1,
        ]);
        Usuario::find(6)->assignRole('coordinador-area');
        RoleScopeUsuario::firstOrCreate([
            'role_id' => 6,
            'scope_id' => 6,
            'usuario_id' => 6,
            'entity_type' => Area::class,
            'entity_id' => 1,
        ]);
        Usuario::find(7)->assignRole('coordinador-seccion');
        RoleScopeUsuario::firstOrCreate([
            'role_id' => 7,
            'scope_id' => 4,
            'usuario_id' => 7,
            'entity_type' => Seccion::class,
            'entity_id' => 1,
        ]);
        Usuario::find(8)->assignRole('docente');
        /*RoleScopeUsuario::create([
            'role_id' => 8,
            'scope_id' => 5,
            'usuario_id' => 8,
            'entity_type' => Curso::class,
            'entity_id' => 1,
        ]);*/
        Usuario::find(9)->assignRole('director');
        RoleScopeUsuario::firstOrCreate([
            'role_id' => 5,
            'scope_id' => 3,
            'usuario_id' => 9,
            'entity_type' => Especialidad::class,
            'entity_id' => 1,
        ]);
        Usuario::find(10)->assignRole('estudiante');
        /*RoleScopeUsuario::create([
            'role_id' => 10,
            'scope_id' => 5,
            'usuario_id' => 10,
            'entity_type' => Curso::class,
            'entity_id' => 1,
        ]);*/
    }
}
