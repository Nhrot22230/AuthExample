<?php
namespace Database\Factories\Matricula;

use App\Models\Matricula\CartaPresentacionSolicitud;
use App\Models\Matricula\Horario;
use App\Models\Usuarios\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartaPresentacionSolicitudFactory extends Factory
{
    protected $model = CartaPresentacionSolicitud::class;

    public function definition()
    {
        $estudiante = Estudiante::factory()->create();
        
        return [
            'estudiante_id' => $estudiante->id,
            'horario_id' => Horario::factory(),
            'especialidad_id' => $estudiante->especialidad_id,
            'estado' => $this->faker->randomElement([ 'Pendiente Secretaria', 'Pendiente de Actividades','Pendiente Firma DC', 'Aprobado', 'Rechazado']),
            'motivo' => $this->faker->sentence(),
            'motivo_rechazo' => $this->faker->optional()->sentence(),
            'file_id' => null,
        ];
    }
}
