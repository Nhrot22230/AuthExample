<?php

namespace Database\Seeders;

use App\AccessPath;
use App\Models\Authorization\PermissionCategory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission_categories = [
            ['name' => 'instituciones', 'access_path' => AccessPath::CONFIGURACION],
            ['name' => 'semestres', 'access_path' => AccessPath::SEMESTRES],
            ['name' => 'areas', 'access_path' => AccessPath::UNIDADES],
            ['name' => 'facultades', 'access_path' => AccessPath::UNIDADES],
            ['name' => 'departamentos', 'access_path' => AccessPath::UNIDADES],
            ['name' => 'especialidades', 'access_path' => AccessPath::UNIDADES],
            ['name' => 'secciones', 'access_path' => AccessPath::UNIDADES],
            ['name' => 'cursos', 'access_path' => AccessPath::CURSOS],
            ['name' => 'planes de estudio', 'access_path' => AccessPath::UNIDADES],
            ['name' => 'horarios', 'access_path' => AccessPath::CURSOS],
            ['name' => 'usuarios', 'access_path' => AccessPath::PERSONAS],
            ['name' => 'roles', 'access_path' => AccessPath::PERSONAS],
            ['name' => 'permisos', 'access_path' => AccessPath::PERSONAS],
        ];

        foreach ($permission_categories as $permission_category) {
            $category = PermissionCategory::create($permission_category);
            $permissions = [
                ['name' => 'ver ' . $permission_category['name'], 'category_id' => $category->id],
                ['name' => 'manage ' . $permission_category['name'], 'category_id' => $category->id],
            ];

            foreach ($permissions as $permission) {
                Permission::create($permission);
            }
        }
    }
}
