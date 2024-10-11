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
            'administrativo',
            'secretarioAcademico',
            'estudiante',
            'docente',
            'jefePractica',
        ];

        $permissions = [
            ['name' => 'ver instituciones', 'category' => 'instituciones'],
            ['name' => 'crear instituciones', 'category' => 'instituciones'],
            ['name' => 'editar instituciones', 'category' => 'instituciones'],
            ['name' => 'eliminar instituciones', 'category' => 'instituciones'],

            ['name' => 'ver semestres', 'category' => 'semestres'],
            ['name' => 'crear semestres', 'category' => 'semestres'],
            ['name' => 'editar semestres', 'category' => 'semestres'],
            ['name' => 'eliminar semestres', 'category' => 'semestres'],

            ['name' => 'ver facultades', 'category' => 'facultades'],
            ['name' => 'crear facultades', 'category' => 'facultades'],
            ['name' => 'editar facultades', 'category' => 'facultades'],
            ['name' => 'eliminar facultades', 'category' => 'facultades'],

            ['name' => 'ver departamentos', 'category' => 'departamentos'],
            ['name' => 'crear departamentos', 'category' => 'departamentos'],
            ['name' => 'editar departamentos', 'category' => 'departamentos'],
            ['name' => 'eliminar departamentos', 'category' => 'departamentos'],

            ['name' => 'ver especialidades', 'category' => 'especialidades'],
            ['name' => 'crear especialidades', 'category' => 'especialidades'],
            ['name' => 'editar especialidades', 'category' => 'especialidades'],
            ['name' => 'eliminar especialidades', 'category' => 'especialidades'],

            ['name' => 'ver secciones', 'category' => 'secciones'],
            ['name' => 'crear secciones', 'category' => 'secciones'],
            ['name' => 'editar secciones', 'category' => 'secciones'],
            ['name' => 'eliminar secciones', 'category' => 'secciones'],

            ['name' => 'ver administrativos', 'category' => 'administrativos'],
            ['name' => 'crear administrativos', 'category' => 'administrativos'],
            ['name' => 'editar administrativos', 'category' => 'administrativos'],
            ['name' => 'eliminar administrativos', 'category' => 'administrativos'],

            ['name' => 'ver usuarios', 'category' => 'usuarios'],
            ['name' => 'crear usuarios', 'category' => 'usuarios'],
            ['name' => 'editar usuarios', 'category' => 'usuarios'],
            ['name' => 'eliminar usuarios', 'category' => 'usuarios'],

            ['name' => 'ver estudiantes', 'category' => 'estudiantes'],
            ['name' => 'crear estudiantes', 'category' => 'estudiantes'],
            ['name' => 'editar estudiantes', 'category' => 'estudiantes'],
            ['name' => 'eliminar estudiantes', 'category' => 'estudiantes'],

            ['name' => 'ver docentes', 'category' => 'docentes'],
            ['name' => 'crear docentes', 'category' => 'docentes'],
            ['name' => 'editar docentes', 'category' => 'docentes'],
            ['name' => 'eliminar docentes', 'category' => 'docentes'],

            ['name' => 'ver roles', 'category' => 'roles'],
            ['name' => 'crear roles', 'category' => 'roles'],
            ['name' => 'editar roles', 'category' => 'roles'],
            ['name' => 'asignar roles', 'category' => 'roles'],
            ['name' => 'revocar roles', 'category' => 'roles'],
            ['name' => 'eliminar roles', 'category' => 'roles'],

            ['name' => 'ver permisos', 'category' => 'permisos'],
            ['name' => 'crear permisos', 'category' => 'permisos'],
            ['name' => 'editar permisos', 'category' => 'permisos'],
            ['name' => 'asignar permisos', 'category' => 'permisos'],
            ['name' => 'revocar permisos', 'category' => 'permisos'],
            ['name' => 'eliminar permisos', 'category' => 'permisos'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name'], 'category' => $permission['category']]);
        }
        $administrativoRole = Role::findByName('administrativo');
        $administrativoRole->givePermissionTo(Permission::all());
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

        $admin = Usuario::find(1);
        if ($admin) {
            $admin->assignRole('administrativo');
        }
    }
}
