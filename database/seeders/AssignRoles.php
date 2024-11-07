<?php

namespace Database\Seeders;

use App\Models\Authorization\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignRoles extends Seeder
{
    public function run(): void
    {
        $admin_role = Role::findByName('Administrador');
        $admin_role->syncPermissions(Permission::all());
    }
}
