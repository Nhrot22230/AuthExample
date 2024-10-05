<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin',
            'secretarioAcademico',
            'estudiante',
            'docente',
            'jefePractica',
        ];

        $permissions = [
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
            'ver roles',
            'crear roles',
            'editar roles',
            'eliminar roles',
            'asignar roles',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());

        $secretarioRole = Role::findByName('secretarioAcademico');
        $userPermissions = Permission::where('name', 'like', '%usuarios%')->get();
        $secretarioRole->givePermissionTo($userPermissions);

        $verUsuariosPermission = Permission::where('name', 'ver usuarios')->first();

        $docenteRole = Role::findByName('docente');
        $docenteRole->givePermissionTo($verUsuariosPermission);

        $jefePracticaRole = Role::findByName('jefePractica');
        $jefePracticaRole->givePermissionTo($verUsuariosPermission);


        $admin = Usuario::find(1);
        $admin->assignRole('admin');
    }
}
