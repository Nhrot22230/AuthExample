<?php

namespace Database\Seeders;

use App\AccessPath;
use App\Models\Authorization\PermissionCategory;
use Illuminate\Database\Seeder;
use App\Models\Authorization\Permission;
use App\Models\Authorization\Scope;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scopes = [
            [
                'name' => 'configuracion_sistema',
                'access_path' => AccessPath::CONFIGURACION_SISTEMA->value,
                'sub_permissions' => [
                    'usuarios',
                    'unidades',
                    'autorizacion',
                    'semestres',
                ]
            ],
            [
                'name' => 'tramites_academicos',
                'access_path' => AccessPath::TRAMITES_ACADEMICOS->value,
                'sub_permissions' => [
                    'pedido-cursos',
                    'jurado-tesis',
                    'matricula-adicional'
                ]
            ],
            [
                'name' => 'mis_solicitudes',
                'access_path' => AccessPath::MIS_SOLICITUDES->value,
                'sub_permissions' => [
                    'mis-encuestas',
                    'mis-matriculas-adicionales',
                    'mis-tema-tesis',
                ]
            ],
            [
                'name' => 'mis_unidades',
                'access_path' => AccessPath::MIS_UNIDADES->value,
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
                'access_path' => AccessPath::GESTION_CONVOCATORIAS->value,
                'sub_permissions' => [
                    'gestion-convocatorias',
                ]
            ],
            [
                'name' => 'evaluar_candidatos',
                'access_path' => AccessPath::EVALUAR_CANDIDATOS->value,
                'sub_permissions' => [
                    'evaluar-candidatos',
                ]
            ],
            [
                'name' => 'mis_convocatorias',
                'access_path' => AccessPath::MIS_CONVOCATORIAS->value,
                'sub_permissions' => [
                    'mis-convocatorias',
                ]
            ],
            [
                'name' => 'mis_cursos',
                'access_path' => AccessPath::MIS_CURSOS->value,
                'sub_permissions' => [
                    'mis-cursos',
                    'mis-horarios',
                ]
            ],
            [
                'name' => 'gestion_alumnos',
                'access_path' => AccessPath::GESTION_ALUMNOS->value,
                'sub_permissions' => [
                    'gestion-alumnos',
                ]
            ],
            [
                'name' => 'gestion-profesores-jps',
                'access_path' => AccessPath::GESTION_PROFESORES_JPS->value,
                'sub_permissions' => [
                    'gestion-profesores-jps',
                ]
            ]
        ];

        // Crear los scopes y permisos
        foreach ($scopes as $scopeData) {
            // Crear un nuevo scope
            $scope = Scope::create([
                'name' => $scopeData['name'],
            ]);

            // Crear los permisos asociados a este scope
            foreach ($scopeData['sub_permissions'] as $permissionName) {
                Permission::create([
                    'name' => $permissionName,
                    'scope_id' => $scope->id // Asociar el permiso al scope recién creado
                ]);
            }
        }
    }
}
