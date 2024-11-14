<?php

namespace Database\Seeders;

use App\Models\Usuarios\Administrativo;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usuario::create([
            'nombre' => 'admin',
            'apellido_paterno' => 'admin',
            'apellido_materno' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'estado' => 'activo',
        ]);
        
        $factor = 2;
        Docente::factory()->count(5 * $factor)->create();
        Estudiante::factory()->count(20 * $factor)->create();
        Administrativo::factory()->count(1 * $factor)->create();
    }
}
