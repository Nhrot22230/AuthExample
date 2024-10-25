<?php

namespace Database\Seeders;

use App\Models\Administrativo;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Usuario::create([
            'nombre' => 'admin',
            'apellido_paterno' => 'admin',
            'apellido_materno' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'estado' => 'activo',
        ]);
        Administrativo::create([
            'usuario_id' => $admin->id,
            'codigoAdministrativo' => 'admin',
            'lugarTrabajo' => 'admin',
            'cargo' => 'admin',
        ]);

        $factor = 2;

        Docente::factory(8 * $factor)->create();
        Estudiante::factory(50 * $factor)->create();
        Administrativo::factory(1 *$factor)->create();
    }
}
