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
            $table->timestamps();
        });

        Schema::create('preguntas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_respuesta', ['likert', 'porcentaje', 'texto']);
            $table->string('texto_pregunta');
            $table->enum('tipo_encuesta', ['docente', 'jefe_practica']);
            $table->timestamps();
        });

        Schema::create('encuesta_pregunta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')->constrained('encuestas')->onDelete('cascade');
            $table->foreignId('pregunta_id')->constrained('preguntas')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('encuesta_curso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')->constrained('encuestas')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuestas');
        Schema::dropIfExists('preguntas');
        Schema::dropIfExists('encuesta_pregunta');
        Schema::dropIfExists('encuesta_curso');
    }
};
