<?php

namespace Database\Seeders;

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
            'directorCarrera',
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

            ['name' => 'ver areas', 'category' => 'areas'],
            ['name' => 'crear areas', 'category' => 'areas'],
            ['name' => 'editar areas', 'category' => 'areas'],
            ['name' => 'eliminar areas', 'category' => 'areas'],

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

            ['name' => 'ver cursos', 'category' => 'cursos'],
            ['name' => 'crear cursos', 'category' => 'cursos'],
            ['name' => 'editar cursos', 'category' => 'cursos'],
            ['name' => 'eliminar cursos', 'category' => 'cursos'],

            ['name' => 'ver planes de estudio', 'category' => 'planes de estudio'],
            ['name' => 'crear planes de estudio', 'category' => 'planes de estudio'],
            ['name' => 'editar planes de estudio', 'category' => 'planes de estudio'],
            ['name' => 'eliminar planes de estudio', 'category' => 'planes de estudio'],

            ['name' => 'ver horarios', 'category' => 'horarios'],
            ['name' => 'crear horarios', 'category' => 'horarios'],
            ['name' => 'editar horarios', 'category' => 'horarios'],
            ['name' => 'eliminar horarios', 'category' => 'horarios'],

            ['name' => 'ver areas', 'category' => 'areas'],
            ['name' => 'crear areas', 'category' => 'areas'],
            ['name' => 'editar areas', 'category' => 'areas'],
            ['name' => 'eliminar areas', 'category' => 'areas'],

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

            ['name' => 'ver temas de tesis', 'category' => 'tema de tesis'],
            ['name' => 'crear temas de tesis', 'category' => 'tema de tesis'],
            ['name' => 'editar temas de tesis', 'category' => 'tema de tesis'],
            ['name' => 'eliminar temas de tesis', 'category' => 'tema de tesis'],

            ['name' => 'ver horarios', 'category' => 'horarios'],
            ['name' => 'crear horarios', 'category' => 'horarios'],
            ['name' => 'editar horarios', 'category' => 'horarios'],
            ['name' => 'eliminar horarios', 'category' => 'horarios'],

            ['name' => 'ver jurados', 'category' => 'jurados'],
            ['name' => 'crear jurados', 'category' => 'jurados'],
            ['name' => 'editar jurados', 'category' => 'jurados'],
            ['name' => 'eliminar jurados', 'category' => 'jurados'],

            ['name' => 'ver asesores', 'category' => 'asesores'],
            ['name' => 'crear asesores', 'category' => 'asesores'],
            ['name' => 'editar asesores', 'category' => 'asesores'],
            ['name' => 'eliminar asesores', 'category' => 'asesores'],

            ['name' => 'ver jefes de práctica', 'category' => 'jefes de práctica'],
            ['name' => 'crear jefes de práctica', 'category' => 'jefes de práctica'],
            ['name' => 'editar jefes de práctica', 'category' => 'jefes de práctica'],
            ['name' => 'eliminar jefes de práctica', 'category' => 'jefes de práctica'],

            ['name' => 'ver observaciones', 'category' => 'observaciones'],
            ['name' => 'crear observaciones', 'category' => 'observaciones'],
            ['name' => 'editar observaciones', 'category' => 'observaciones'],
            ['name' => 'eliminar observaciones', 'category' => 'observaciones'],

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

            ['name' => 'crear matriculas_adicionales', 'category' => 'solicitudes'],
            ['name' => 'ver matriculas_especialidad', 'category' => 'solicitudes'],
            ['name' => 'ver mis matriculas_adicionales', 'category' => 'solicitudes'],
            ['name' => 'ver matriculas_facultad', 'category' => 'solicitudes'],
            
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name'], 'category' => $permission['category']]);
        }

        $role = Role::findByName('administrativo');
        $permissions = Permission::all();
        $role->syncPermissions($permissions);

        $role = Role::findByName('secretarioAcademico');
        $permissions = Permission::where('category', '=', 'planes de estudio')
            ->orWhere('category', '=', 'horarios')
            ->orWhere('category', '=', 'areas')
            ->orWhere('category', '=', 'departamentos')
            ->orWhere('category', '=', 'facultades')
            ->orWhere('category', '=', 'tema de tesis') // Corrige la categoría aquí
            ->orWhere('category', '=', 'solicitudes')
            ->orWhere('category', '=', 'cursos')

            ->get();

        $role->syncPermissions($permissions);


        $role = Role::findByName('directorCarrera');
        $permissions = Permission::where('category', '=', 'planes de estudio')
            ->orWhere('category', '=', 'horarios')
            ->orWhere('category', '=', 'areas')
            ->orWhere('category', '=', 'departamentos')
            ->orWhere('category', '=', 'facultades')
            ->orWhere('category', '=', 'tema de tesis')
            ->orWhere('category', '=', 'solicitudes') 
            ->orWhere('category', '=', 'cursos') 
            ->orWhere('category', '=', 'jefes de práctica')
            ->orWhere('category', '=', 'instituciones')
            ->orWhere('category', '=', 'especialidades') 
            ->get();
        $role->syncPermissions($permissions);

        $role = Role::findByName('docente');
        $permissions = Permission::where('name', 'like', 'ver %')
            ->orWhere('name', 'like', 'editar %')
            ->get();
        $role->syncPermissions($permissions);

        $role = Role::findByName('estudiante');
        $permissions = Permission::where('name', 'like', 'ver %')
            ->get();
        $role->syncPermissions($permissions);
    }
}
