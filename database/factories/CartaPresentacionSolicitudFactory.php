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
        // Obtener un estudiante existente en la base de datos
        $estudiante = Estudiante::inRandomOrder()->first();  // Trae un estudiante aleatorio

        // Si no hay estudiantes en la base de datos, crear uno para el test
        if (!$estudiante) {
            $estudiante = Estudiante::factory()->create();
        }

        return [
            'estudiante_id' => $estudiante->id,  // Asignamos el id del estudiante
            'horario_id' => Horario::factory(),  // Creamos un horario aleatorio
            'especialidad_id' => $estudiante->especialidad_id,  // Asignamos la especialidad del estudiante
            'estado' => $this->faker->randomElement([ 'Pendiente Secretaria', 'Pendiente Firma DC', 'Aprobado', 'Rechazado']),
            'motivo' => $this->faker->sentence(),
            'motivo_rechazo' => $this->faker->optional()->sentence(),  // Se hace opcional solo si el estado es "Rechazado"
            'pdf_solicitud' => null,
            'pdf_firmado' => null,
        ];
    }
}
