<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
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

        Usuario::factory()
            ->count(50)
            ->create();
    }
}
