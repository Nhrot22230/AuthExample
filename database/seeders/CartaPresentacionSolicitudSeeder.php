<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Matricula\CartaPresentacionSolicitud;
use Faker\Factory as Faker;

class CartaPresentacionSolicitudSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Instanciamos Faker para generar datos aleatorios
        $faker = Faker::create();

        // Obtenemos todos los estudiantes que tienen al menos un horario matriculado
        $estudiantesConHorarios = DB::table('estudiante_horario')
            ->select('estudiante_id')
            ->distinct()  // Asegura que solo se devuelvan estudiantes únicos
            ->get();

        // Recorremos todos los estudiantes que tienen al menos un horario
        foreach ($estudiantesConHorarios as $estudianteMatriculado) {
            // Verificamos si el estudiante tiene al menos un horario asignado
            $horarios = DB::table('estudiante_horario')->where('estudiante_id', $estudianteMatriculado->estudiante_id)->get();

            if ($horarios->isEmpty()) {
                continue;  // Si no tiene horarios, pasamos al siguiente estudiante
            }

            // Elegimos un horario aleatorio entre los horarios asignados al estudiante
            $horarioId = $horarios->random()->horario_id;

            // Decidir aleatoriamente si la solicitud será "Rechazada" o "Pendiente"
            $estado = $faker->randomElement([ 'Pendiente Secretaria', 'Pendiente de Actividades','Pendiente Firma DC', 'Aprobado', 'Rechazado']);
            $motivoRechazo = null;

            // Si el estado es "Rechazado", asignamos un motivo de rechazo
            if ($estado === 'Rechazado') {
                $motivoRechazo = $faker->sentence;  // Asignar un motivo de rechazo aleatorio
            }

            // Crear la solicitud con el estado y el motivo de rechazo si aplica
            CartaPresentacionSolicitud::create([
                'estudiante_id' => $estudianteMatriculado->estudiante_id,  // Estudiante de la matrícula
                'horario_id' => $horarioId,        // Horario de la matrícula
                'especialidad_id' => DB::table('estudiantes')->where('id', $estudianteMatriculado->estudiante_id)->value('especialidad_id'), // Obtener especialidad_id del estudiante
                'estado' => $estado,                           // Estado aleatorio
                'motivo' => $faker->text(200),                 // Motivo de la solicitud generado aleatoriamente
                'motivo_rechazo' => $motivoRechazo,            // Motivo de rechazo si aplica
                // 'pdf_solicitud' => null,                       // No hay PDF aún
                // 'pdf_firmado' => null,                         // No hay PDF firmado aún
            ]);
        }
    }
}
