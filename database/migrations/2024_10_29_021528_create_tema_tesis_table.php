<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('resumen');
            $table->string('documento_url');
            $table->enum('estado', ['pendiente', 'aceptado', 'desaprobado'])->default('pendiente');
            $table->foreignId('especialidad_id')->constrained('especialidades');
            $table->enum('estadoJurado', ['enviado', 'no enviado', 'aprobado', 'desaprobado', 'pendiente', 'vencido'])->default('no enviado');
            $table->date('fechaEnvio');
            $table->timestamps();
        });

        Schema::create('tema_tesis_jurado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_tesis')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->enum('estado', ['aprobado', 'desaprobado', 'pendiente'])->default('pendiente');
            $table->timestamps();
        });

        Schema::create('tema_tesis_asesor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_tesis')->onDelete('cascade');
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('tema_tesis_autor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_tesis')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tema_tesis_autores');
        Schema::dropIfExists('tema_tesis_asesor');
        Schema::dropIfExists('tema_tesis_jurado');
        Schema::dropIfExists('tema_tesis');
    }
};
