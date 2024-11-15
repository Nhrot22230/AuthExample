<?php

namespace Database\Seeders;

use App\Models\Authorization\Permission;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignRoles extends Seeder
{
    public function run(): void
    {
        $admin_role = Role::findByName('administrador');
        $admin_role->syncPermissions(Permission::all());
        Usuario::find(1)->assignRole('administrador');


        $director_role = Role::findByName('director');
        $permisos_director = Permission::where('name', 'like', '%especialidades%')
            ->orWhere('name', 'like', '%solicitudes%')
            ->orWhere('name', 'like', 'encuestas')
            ->orWhere('name', 'like', '%temas%')
            ->orWhere('name', 'like', '%unidades%')
            ->get();
        $director_role->syncPermissions($permisos_director);

        $estudiante_role = Role::findByName('estudiante');
        $permisos_estudiante = Permission::where('name', 'like', '%encuestas%')
            ->orWhere('name', 'like', 'mis unidades')
            ->get();
        $estudiante_role->syncPermissions($permisos_estudiante);

        Estudiante::all()->each(function ($estudiante) {
            $estudiante->usuario->assignRole('estudiante');
        });
    }
}
