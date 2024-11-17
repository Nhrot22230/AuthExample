<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tema_de_tesis', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('resumen');
            $table->unsignedBigInteger('file_id')->nullable();
            $table->enum('estado', ['aprobado', 'pendiente', 'desaprobado'])->default('pendiente');
            $table->enum('estado_jurado', ['enviado', 'no enviado', 'aprobado', 'pendiente', 'desaprobado', 'vencido'])->default('pendiente');
            $table->date('fecha_enviado')->nullable();
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->text('comentarios')->nullable();
            $table->timestamps();
        });

        // Tabla intermedia para estudiantes
        Schema::create('estudiante_tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('tema_tesis_id')->constrained('tema_de_tesis')->onDelete('cascade');
        });

        // Tabla intermedia para asesores (docentes)
        Schema::create('asesor_tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_de_tesis')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
        });

        // Tabla intermedia para jurados (docentes)
        Schema::create('jurado_tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_de_tesis')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
        });

        Schema::create('proceso_aprobacion_tema', function(Blueprint $table){
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_de_tesis')->onDelete('cascade');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado_proceso', ['aprobado', 'pendiente', 'rechazado'])->default('pendiente');
            $table->timestamps();
        });

        Schema::create('estado_aprobacion_tema', function(Blueprint $table){
            $table->id();
            $table->foreignId('proceso_aprobacion_id')->constrained('proceso_aprobacion_tema')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->enum('responsable', ['asesor', 'coordinador', 'director'])->default('asesor');
            $table->enum('estado', ['aprobado', 'pendiente', 'rechazado'])->default('pendiente');
            $table->date('fecha_decision')->nullable();
            $table->text('comentarios')->nullable();
            $table->unsignedBigInteger('file_id')->nullable(); // Define el campo como nullable
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurado_tema_tesis');
        Schema::dropIfExists('asesor_tema_tesis');
        Schema::dropIfExists('estudiante_tema_tesis');
        Schema::dropIfExists('estado_aprobacion_tema');
        Schema::dropIfExists('proceso_aprobacion_tema');
        Schema::dropIfExists('tema_de_tesis');
    }
};
