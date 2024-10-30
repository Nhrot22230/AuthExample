<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('nombre_encuesta');
            $table->enum('tipo_encuesta', ['docente', 'jefe_practica']);
            $table->boolean('disponible');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('preguntas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_respuesta', ['likert', 'porcentaje', 'texto']);
            $table->string('texto_pregunta');
            $table->timestamps();
        });

        Schema::create('encuesta_pregunta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')->constrained('encuestas')->onDelete('cascade');
            $table->foreignId('pregunta_id')->constrained('preguntas')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('encuesta_horario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')->constrained('encuestas')->onDelete('cascade');
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuesta_pregunta');
        Schema::dropIfExists('encuesta_horario');
        Schema::dropIfExists('encuestas');
        Schema::dropIfExists('preguntas');
    }
};
