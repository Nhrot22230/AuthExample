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
            $table->string('documento')->nullable();
            $table->enum('estado', ['aprobado', 'pendiente', 'desaprobado'])->default('pendiente');
            $table->enum('estado_jurado', ['enviado', 'no enviado', 'aprobado', 'pendiente', 'desaprobado', 'vencido'])->default('pendiente');
            $table->date('fecha_enviado')->nullable();
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->text('comentarios')->nullable();
            $table->timestamps();
        });

        // Tabla intermedia para estudiantes
        Schema::create('estudiante_tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_de_tesis')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained()->onDelete('cascade');
        });

        // Tabla intermedia para asesores (docentes)
        Schema::create('asesor_tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_de_tesis')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained()->onDelete('cascade');
        });

        // Tabla intermedia para jurados (docentes)
        Schema::create('jurado_tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_de_tesis')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurado_tema_tesis');
        Schema::dropIfExists('asesor_tema_tesis');
        Schema::dropIfExists('estudiante_tema_tesis');
        Schema::dropIfExists('tema_de_tesis');
    }
};
