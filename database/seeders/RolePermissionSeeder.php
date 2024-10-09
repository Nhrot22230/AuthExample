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
            'administrativo',
        ];

        $permissions = [
            // Permisos de usuarios
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',

            // Permisos de estudiantes
            'ver estudiantes',
            'crear estudiantes',
            'editar estudiantes',
            'eliminar estudiantes',

            // Permisos de docentes
            'ver docentes',
            'crear docentes',
            'editar docentes',
            'eliminar docentes',

            // Permisos de administrativos
            'ver administrativos',
            'crear administrativos',
            'editar administrativos',
            'eliminar administrativos',

            // Permisos de roles y permisos
            'ver roles',
            'crear roles',
            'editar roles',
            'eliminar roles',
            'asignar roles',
            'revocar roles',
            'ver permisos',
            'crear permisos',
            'editar permisos',
            'eliminar permisos',
            'asignar permisos',
            'revocar permisos',
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
        $secretarioRole->givePermissionTo([
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'ver estudiantes',
            'crear estudiantes',
            'editar estudiantes',
            'ver docentes',
            'crear docentes',
            'editar docentes',
        ]);
        
        $docenteRole = Role::findByName('docente');
        $docenteRole->givePermissionTo([
            'ver estudiantes',
            'ver usuarios',
            'ver docentes',
        ]);

        $jefePracticaRole = Role::findByName('jefePractica');
        $jefePracticaRole->givePermissionTo([
            'ver estudiantes',
        ]);

        $administrativoRole = Role::findByName('administrativo');
        $administrativoRole->givePermissionTo([
            'ver usuarios',
            'ver administrativos',
        ]);

        $admin = Usuario::find(1);
        if ($admin) {
            $admin->assignRole('admin');
        }
    }
}
