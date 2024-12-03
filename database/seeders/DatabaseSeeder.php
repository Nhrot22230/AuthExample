<?php

namespace Database\Seeders;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UniversidadSeeder::class);
        $this->call(UsuariosSeeder::class);
        // Flujo para Sofia
        $this->call(FlujoEncuestasSeeder::class);
        $this->call(CartaPresentacionSolicitudSeeder::class);
        // Pedido
        $this->call(PedidoCursosSeeder::class);

        // Convocatorias
        $this->call(ProcesoConvocatoriaSeeder::class);
        $this->call(FlujoTemaTesisSeeder::class);

        // Plan de estudios todas las especialidades
        $this->call(PlanesEspecialidadSeeder::class);

        // ASIGNACION DE ROLES
        $this->call(AssignRoles::class);
    }
}
