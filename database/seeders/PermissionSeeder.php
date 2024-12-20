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
            [
                'name' => 'configuracion_sistema',
                'access_path' => AccessPath::CONFIGURACION_SISTEMA,
                'sub_permissions' => [
                    'usuarios',
                    'unidades',
                    'autorizacion',
                    'semestres',
                ]
            ],
            [
                'name' => 'mis_solicitudes',
                'access_path' => AccessPath::MIS_SOLICITUDES,
                'sub_permissions' => [
                    'mis-encuestas',
                    'mis-matriculas-adicionales',
                    'mis-tema-tesis',
                ]
            ],
            [
                'name' => 'mis_unidades',
                'access_path' => AccessPath::MIS_UNIDADES,
                'sub_permissions' => [
                    'mis-unidades',
                    'facultades',
                    'departamentos',
                    'especialidades',
                    'secciones',
                    'areas',
                    'cursos',
                ]
            ],
            [
                'name' => 'gestion_convocatorias',
                'access_path' => AccessPath::GESTION_CONVOCATORIAS,
                'sub_permissions' => [
                    'gestion-convocatorias',
                ]
            ],
            [
                'name' => 'evaluar_candidatos',
                'access_path' => AccessPath::EVALUAR_CANDIDATOS,
                'sub_permissions' => [
                    'evaluar-candidatos',
                ]
            ],
            [
                'name' => 'mis_convocatorias',
                'access_path' => AccessPath::MIS_CONVOCATORIAS,
                'sub_permissions' => [
                    'mis-convocatorias',
                ]
            ],
            [
                'name' => 'mis_cursos',
                'access_path' => AccessPath::MIS_CURSOS,
                'sub_permissions' => [
                    'mis-cursos',
                    'mis-horarios',
                ]
            ],
            [
                'name' => 'gestion_alumnos',
                'access_path' => AccessPath::GESTION_ALUMNOS,
                'sub_permissions' => [
                    'gestion-alumnos',
                ]
            ],
            [
                'name' => 'gestion-profesores-jps',
                'access_path' => AccessPath::GESTION_PROFESORES_JPS,
                'sub_permissions' => [
                    'gestion-profesores-jps',
                ]
                ],
            [
                'name' => 'permisos_facultad',
                'access_path' => AccessPath::FACULTAD,
                'sub_permissions' => [
                    'proceso pedido de cursos',
                    'proceso solicitud de jurado',
                    'proceso matricula adicional',
                ]
            ],
        ];

        foreach ($permission_categories as $category) {
            $permission_category = PermissionCategory::create([
                'name' => $category['name'],
                'access_path' => $category['access_path'],
            ]);

            foreach ($category['sub_permissions'] as $permission_name) {
                Permission::create([
                    'name' => $permission_name,
                    'permission_category_id' => $permission_category->id
                ]);
            }
        }
    }
}
