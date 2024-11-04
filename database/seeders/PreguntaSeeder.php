<?php

namespace Database\Seeders;

use App\Models\Encuesta;
use App\Models\Pregunta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Seeder;

class PreguntaSeeder extends Seeder
{

    public function run(): void
    {
        //Pregunta::factory(10)->create();
        $preguntas = [
            ['texto_pregunta' => '¿El profesor demuestra dominio del contenido del curso?',
                'tipo_respuesta' => 'likert',
                'tipo_pregunta' => 'profesor'],
            ['texto_pregunta' => '¿El profesor es puntual al iniciar y terminar las clases?',
                'tipo_respuesta' => 'likert',
                'tipo_pregunta' => 'profesor'],
            ['texto_pregunta' => '¿El profesor proporciona retroalimentación útil sobre las tareas y exámenes?',
                'tipo_respuesta' => 'likert',
                'tipo_pregunta' => 'profesor'],
            ['texto_pregunta' => '¿El profesor motiva a los estudiantes a mejorar su rendimiento académico?',
                'tipo_respuesta' => 'likert',
                'tipo_pregunta' => 'profesor'],
            ['texto_pregunta' => '¿El profesor está disponible para consultas fuera del horario de clase?',
                'tipo_respuesta' => 'likert',
                'tipo_pregunta' => 'profesor'],

            ['texto_pregunta' => '¿En qué porcentaje el profesor cumple con el temario del curso?',
                'tipo_respuesta' => 'porcentaje',
                'tipo_pregunta' => 'profesor'],
            ['texto_pregunta' => '¿En qué porcentaje consideras que el profesor fomenta un ambiente de respeto en clase?',
                'tipo_respuesta' => 'porcentaje',
                'tipo_pregunta' => 'profesor'],

            ['texto_pregunta' => '¿En qué porcentaje sientes que has cumplido con la asistencia al curso?',
                'tipo_respuesta' => 'porcentaje',
                'tipo_pregunta' => 'estudiante'],
            ['texto_pregunta' => '¿En qué porcentaje consideras que te has esforzado en este curso?',
                'tipo_respuesta' => 'porcentaje',
                'tipo_pregunta' => 'estudiante'],

            ['texto_pregunta' => 'Comentarios adicionales sobre el desempeño del profesor',
                'tipo_respuesta' => 'texto',
                'tipo_pregunta' => 'profesor']
        ];

        foreach ($preguntas as $pregunta) {
            Pregunta::create($pregunta);
        }


    }
}
