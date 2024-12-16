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
            ->orWhere('name', 'mis-unidades')
            ->orWhere('name', 'facultades')
            ->get();
        $secretario_role->syncPermissions($permisos_secretario);

        $asistente_especialidad_role = Role::findByName('asistente-especialidad');
        $permisos_asistente_especialidad = Permission::where('name', 'mis-unidades')
            ->orWhere('name', 'especialidades')
            ->get();
        $asistente_especialidad_role->syncPermissions($permisos_asistente_especialidad);

        $asistente_seccion_role = Role::findByName('asistente-seccion');
        $permisos_asistente_seccion = Permission::where('name', 'mis-unidades')
            ->orWhere('name', 'secciones')
            ->orWhere('name', 'gestion-convocatorias')
            ->orWhere('name', 'gestion-profesores-jps')
            ->get();
        $asistente_seccion_role->syncPermissions($permisos_asistente_seccion);

        $director_role = Role::findByName('director');
        $permisos_director = Permission::where('name', 'mis-unidades')
            ->orWhere('name', 'especialidades')
            ->orWhere('name', 'gestion-alumnos')
            ->get();
        $director_role->syncPermissions($permisos_director);

        $coordinador_area_role = Role::findByName('coordinador-area');
        $permisos_coordinador_area = Permission::where('name', 'mis-unidades')
            ->orWhere('name', 'areas')
            ->get();
        $coordinador_area_role->syncPermissions($permisos_coordinador_area);

        $coordinador_seccion_role = Role::findByName('coordinador-seccion');
        $permisos_coordinador_seccion = Permission::where('name', 'mis-unidades')
            ->orWhere('name', 'secciones')
            ->orWhere('name', 'gestion-convocatorias')
            ->get();
        $coordinador_seccion_role->syncPermissions($permisos_coordinador_seccion);

        $docente_role = Role::findByName('docente');
        $permisos_docente = Permission::where('name', 'mis-cursos')
            ->orWhere('name', 'mis-horarios')
            ->orWhere('name', 'mis-tema-tesis')
            ->orWhere('name', 'gestion-alumnos')
            ->get();
        $docente_role->syncPermissions($permisos_docente);

        /*$jefe_practica_role = Role::findByName('jefe-practica');
        $permisos_jefe_practica = Permission::where('name', 'mis-unidades');
        $jefe_practica_role->syncPermissions($permisos_jefe_practica);*/

        $estudiante_role = Role::findByName('estudiante');
        $permisos_estudiante = Permission::where('name', 'mis-cursos')
            ->orWhere('name', 'mis-horarios')
            ->orWhere('name', 'mis-tema-tesis')
            ->orWhere('name', 'mis-matriculas-adicionales')
            ->orWhere('name', 'mis-encuestas')
            ->get();
        $estudiante_role->syncPermissions($permisos_estudiante);

        $comite_role = Role::findByName('comite');
        $permisos_comite = Permission::where('name', 'evaluar-candidatos')->get();
        $comite_role->syncPermissions($permisos_comite);

        $candidato_role = Role::findByName('candidato');
        $permisos_candidato = Permission::where('name', 'mis-convocatorias')->get();
        $candidato_role->syncPermissions($permisos_candidato);
    }
}
