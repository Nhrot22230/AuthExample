<?php

namespace Database\Factories\Usuarios;

use App\Models\Universidad\Area;
use App\Models\Universidad\Departamento;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Seccion;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocenteFactory extends Factory
{
    protected $model = Docente::class;

    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory(),
            'codigoDocente' => $this->faker->unique()->randomNumber(8),
            'tipo' => $this->faker->randomElement(['TPA', 'TC']),
            'seccion_id' => Seccion::factory(),
            'especialidad_id' => Especialidad::factory(),
            'area_id' => Area::factory(),
        ];
    }
}
