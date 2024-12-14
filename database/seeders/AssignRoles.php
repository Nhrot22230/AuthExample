<?php

namespace Database\Seeders;

use App\Models\Authorization\Permission;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignRoles extends Seeder
{
    public function run(): void
    {
        $admin_role = Role::findByName('administrador');
        $permisos_admin = Permission::where('name', 'usuarios')
            ->orWhere('name', 'unidades')
            ->orWhere('name', 'autorizacion')
            ->orWhere('name', 'semestres')
            ->get();
        $admin_role->syncPermissions($permisos_admin);
        Usuario::find(1)->assignRole('administrador');

        $secretario_role = Role::findByName('secretario-academico');
        $permisos_secretario = Permission::where('name', 'proceso pedido de cursos')
            ->orWhere('name', 'proceso solicitud de jurado')
            ->orWhere('name', 'proceso matricula adicional')
            ->get();
        $secretario_role->syncPermissions($permisos_secretario);

        $asistente_especialidad_role = Role::findByName('asistente-especialidad');
        $permisos_asistente_especialidad = Permission::where('name', 'mis-unidades');
        $asistente_especialidad_role->syncPermissions($permisos_asistente_especialidad);

        $asistente_seccion_role = Role::findByName('asistente-seccion');
        $permisos_asistente_seccion = Permission::where('name', 'mis-unidades');
        $asistente_seccion_role->syncPermissions($permisos_asistente_seccion);

        $director_role = Role::findByName('director');
        $permisos_director = Permission::where('name', 'mis-unidades');
        $director_role->syncPermissions($permisos_director);

        $director_role = Role::findByName('director');
        $permisos_director = Permission::where('name', 'mis-unidades');
        $director_role->syncPermissions($permisos_director);

        $coordinador_area_role = Role::findByName('coordinador-area');
        $permisos_coordinador_area = Permission::where('name', 'mis-unidades');
        $coordinador_area_role->syncPermissions($permisos_coordinador_area);

        $coordinador_seccion_role = Role::findByName('coordinador-seccion');
        $permisos_coordinador_seccion = Permission::where('name', 'mis-unidades');
        $coordinador_seccion_role->syncPermissions($permisos_coordinador_seccion);

        $docente_role = Role::findByName('docente');
        $permisos_docente = Permission::where('name', 'mis-unidades');
        $docente_role->syncPermissions($permisos_docente);

        $jefe_practica_role = Role::findByName('jefe-practica');
        $permisos_jefe_practica = Permission::where('name', 'mis-unidades');
        $jefe_practica_role->syncPermissions($permisos_jefe_practica);

        $estudiante_role = Role::findByName('estudiante');
        $permisos_estudiante = Permission::where('name', 'mis-unidades');
        $estudiante_role->syncPermissions($permisos_estudiante);
    }
}
