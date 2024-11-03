<?php

namespace Database\Seeders;

use App\Models\Authorization\Permission;
use App\Models\Authorization\RoleScope;
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
        
        Role::findByName('Director')->syncPermissions(Permission::where('name', 'like', '%especialidades%')->get());
        
        $director = Docente::inRandomOrder()->first()->usuario;
        $director->assignRole(Role::findByName('Director'));

        RoleScopeUsuario::create([
            'role_id' => Role::where('name', 'Director')->first()->id,
            'scope_id' => Scope::where('name', 'Especialidad')->first()->id,
            'usuario_id' => $director->id,
            'entity_id' => $director->docente->especialidad_id,
        ]);
    }
}
