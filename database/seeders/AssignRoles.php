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
        $admin_role->syncPermissions(Permission::all());
        Usuario::find(1)->assignRole('administrador');


        // DIRECTOR DE CARRERA
        $director_role = Role::findByName('director');
        $permisos_director = Permission::whereIn('permission_category_id', function ($query) {
            $query->select('id')
                ->from('permission_categories')
                ->whereIn('name', ['especialidad', 'mis_unidades', 'mis_convocatorias', 'evaluar_candidatos', 'gestion-alumnos', 'gestion-profesores-jps']);
        })->get();

        $director_role->syncPermissions($permisos_director);

        // COORDINADOR

        $coordinador_role = Role::findByName('coordinador');
        $permisos_coordinador = Permission::where('name', 'like', '%especialidades%')
            ->orWhere('name', 'like', '%solicitudes%')
            ->orWhere('name', 'like', '%encuestas%')
            ->orWhere('name', 'like', '%temas%')
            ->orWhere('name', 'like', '%unidades%')
            ->get();
        $coordinador_role->syncPermissions($permisos_coordinador);

        // SECRETARIO ACADEMICO
        $secretario_role = Role::findByName('secretario-academico');
        $permisos_secretario = Permission::whereIn('permission_category_id', function ($query) {
            $query->select('id')
                ->from('permission_categories')
                ->whereIn('name', ['facultad', 'mis_unidades', 'mis_convocatorias']);
        })->get();
        $secretario_role->syncPermissions($permisos_secretario);

        // ASISTENTE DE SECCION
        $asistenteSeccionRole = Role::findByName('asistente-seccion');
        $permisosAsistenteSeccion = Permission::whereIn('permission_category_id', function ($query) {
            $query->select('id')
                ->from('permission_categories')
                ->whereIn('name', ['mis_convocatorias', 'evaluar_candidatos', 'gestion_convocatorias']);
        })->get();
        $permisosAdicionales = Permission::where('name', 'like', '%gestion-convocatorias%')->get(); // Corrección del error
        $permisosTotales = $permisosAsistenteSeccion->merge($permisosAdicionales);
        $asistenteSeccionRole->syncPermissions($permisosTotales);


        // DOCENTE
        $docente_role = Role::findByName('docente');
        // Obtenemos los permisos que correspondan a los docentes
        $permisos_docente = Permission::where('name', 'like', '%cursos%')
            ->orWhere('name', 'like', '%encuestas%')
            ->orWhere('name', 'like', '%mis_cursos%')
            ->orWhere('name', 'like', '%solicitudes de encuestas%')
            ->orWhere('name', 'like', '%horarios%')
            ->orWhere('name', 'like', '%solicitudes%')
            ->orWhere('name', 'like', '%evaluar-candidatos%')
            ->orWhere('name', 'like', '%mis_convocatorias%')
            ->get();

        // Asignamos los permisos al rol de "docente"
        $docente_role->syncPermissions($permisos_docente);

        // ESTUDIANTE

        $estudiante_role = Role::findByName('estudiante');

        // Obtenemos los permisos que correspondan a los estudiantes
        $permisos_estudiante = Permission::whereIn('name', [
            'mis-cursos',
            'mis-horarios',
            'mis-encuestas',
            'mis-matriculas-adicionales',
            'mis-tema-tesis',
            'mis-convocatorias'
        ])->get();


        // Asignamos los permisos al rol de "estudiante"
        $estudiante_role->syncPermissions($permisos_estudiante);



        $asistente_role = Role::findByName('asistente');

        $comite_role = Role::findByName('comite');
        $permisos_comite = Permission::where('name', 'like', '%convocatorias%')
            ->orWhere('name', 'like', '%comite%')
            ->get();
        $comite_role->syncPermissions($permisos_comite);

        // Obtenemos los permisos que correspondan a los asistentes
        $permisos_asistente = Permission::where('name', 'like', '%mis unidades%')
            ->orWhere('name', 'like', '%unidades%')
            ->orWhere('name', 'like', '%cursos%')
            ->orWhere('name', 'like', '%horarios%')
            ->orWhere('name', 'like', '%docentes%')
            ->orWhere('name', 'like', '%departamentos%')
            ->orWhere('name', 'like', '%facultades%')
            ->orWhere('name', 'like', '%especialidades%')
            ->orWhere('name', 'like', '%solicitudes%')
            ->orWhere('name', 'like', '%convocatorias%')
            ->orWhere('name', 'like', '%roles%')
            ->orWhere('name', 'like', '%usuarios%')
            ->orWhere('name', 'like', '%mis_convocatorias%')
            ->get();

        // Asignamos los permisos al rol de "asistente"
        $asistente_role->syncPermissions($permisos_asistente);
    }
}
