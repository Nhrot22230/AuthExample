<?php

namespace Database\Seeders;

use App\AccessPath;
use App\Models\Authorization\PermissionCategory;
use Illuminate\Database\Seeder;
use App\Models\Authorization\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission_categories = [
            ['name' => 'administrativos',                   'access_path' => AccessPath::PERSONAS],
            ['name' => 'areas',                             'access_path' => AccessPath::UNIDADES],
            ['name' => 'asesores',                          'access_path' => AccessPath::CURSOS],
            ['name' => 'candidaturas',                      'access_path' => AccessPath::CANDIDATURAS],
            ['name' => 'configuración personal',            'access_path' => AccessPath::CONFIGURACION_PERSONAL],
            ['name' => 'cursos',                            'access_path' => AccessPath::CURSOS],
            ['name' => 'departamentos',                     'access_path' => AccessPath::UNIDADES],
            ['name' => 'docentes',                          'access_path' => AccessPath::PERSONAS],
            ['name' => 'encuestas',                         'access_path' => AccessPath::SOLICITUDES_ENCUENTAS],
            ['name' => 'especialidades',                    'access_path' => AccessPath::UNIDADES],
            ['name' => 'estudiantes',                       'access_path' => AccessPath::PERSONAS],
            ['name' => 'facultades',                        'access_path' => AccessPath::UNIDADES],
            ['name' => 'horarios',                          'access_path' => AccessPath::CURSOS],
            ['name' => 'instituciones',                     'access_path' => AccessPath::CONFIGURACION_SISTEMA],
            ['name' => 'jefes de práctica',                 'access_path' => AccessPath::JEFE_PRACTICA],
            ['name' => 'jurados',                           'access_path' => AccessPath::JURADOS_TESIS_SECRETARIO_ACADEMICO],
            ['name' => 'matriculas_adicionales',            'access_path' => AccessPath::MATRICULAS_ADICIONALES],
            ['name' => 'mis matriculas_adicionales',        'access_path' => AccessPath::MATRICULAS_ADICIONALES],
            ['name' => 'matriculas_especialidad',           'access_path' => AccessPath::MATRICULAS_ADICIONALES],
            ['name' => 'mis matriculas_especialidad',       'access_path' => AccessPath::MATRICULAS_ADICIONALES],
            ['name' => 'mis candidaturas',                  'access_path' => AccessPath::MIS_CANDIDATURAS],
            ['name' => 'mis cursos',                        'access_path' => AccessPath::MIS_CURSOS],
            ['name' => 'mis encuestas',                     'access_path' => AccessPath::MIS_ENCUESTAS],
            ['name' => 'mis unidades',                      'access_path' => AccessPath::MIS_UNIDADES],
            ['name' => 'observaciones',                     'access_path' => AccessPath::JURADOS_TESIS_SECRETARIO_ACADEMICO],
            ['name' => 'pedidos de horarios',               'access_path' => AccessPath::PEDIDOS_HORARIOS],
            ['name' => 'permisos',                          'access_path' => AccessPath::CONFIGURACION_SISTEMA],
            ['name' => 'planes de estudio',                 'access_path' => AccessPath::PLAN_ESTUDIOS],
            ['name' => 'roles',                             'access_path' => AccessPath::CONFIGURACION_SISTEMA],
            ['name' => 'secciones',                         'access_path' => AccessPath::UNIDADES],
            ['name' => 'semestres',                         'access_path' => AccessPath::SEMESTRES],
            ['name' => 'solicitudes',                       'access_path' => AccessPath::TRAMITES_ACADEMICOS],
            ['name' => 'solicitudes de encuestas',          'access_path' => AccessPath::SOLICITUDES_ENCUENTAS],
            ['name' => 'temas de tesis',                    'access_path' => AccessPath::JURADOS_TESIS_SECRETARIO_ACADEMICO],
            ['name' => 'usuarios',                          'access_path' => AccessPath::PERSONAS],
            ['name' => 'convocatorias',                     'access_path' => AccessPath::CONVOCATORIAS],
            ['name' => 'postulante',                        'access_path' => AccessPath::POSTULANTE],
            ['name' => 'comite',                            'access_path' => AccessPath::COMITE],
        ];

        foreach ($permission_categories as $pc) {
            $permission_category = PermissionCategory::create($pc);
            
            Permission::create([
                'name' => 'ver ' . $pc['name'],
                'permission_category_id' => $permission_category->id
            ]);

            Permission::create([
                'name' => 'manage ' . $pc['name'],
                'permission_category_id' => $permission_category->id
            ]);
        }
    }
}
