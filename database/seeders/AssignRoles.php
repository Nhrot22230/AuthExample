<?php

namespace Database\Seeders;

use App\Models\Authorization\Permission;
use App\Models\Authorization\RoleScopeUsuario;
use App\Models\Authorization\Scope;
use App\Models\Docente;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignRoles extends Seeder
{
    public function run(): void
    {
        Role::findByName('Administrador')->syncPermissions(Permission::all());
        
        $admin = Usuario::find(1);
        $admin->assignRole(Role::findByName('Administrador'));

        $director = Docente::inRandomOrder()->first()->usuario;
        $director_role = Role::where('name', 'Director de Carrera')->first();
        $director->assignRole($director_role);

        RoleScopeUsuario::create([
            'role_id' => $director_role->id,
            'scope_id' => Scope::where('name', 'Especialidad')->first()->id,
            'usuario_id' => $director->id,
            'entity_id' => $director->docente->especialidad_id,
            'entity_type' => 'App\Models\Especialidad',
        ]);
    }
}
