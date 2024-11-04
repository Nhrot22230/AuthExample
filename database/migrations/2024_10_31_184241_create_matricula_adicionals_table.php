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
        Schema::create('matricula_adicionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade'); // Relación con la tabla estudiantes
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade'); // Relación con la tabla especialidades
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade'); // Relación con la tabla estudiantes
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade'); // Relación con la tabla especialidades
            $table->string('motivo');

            $table->text('justificacion');
            $table->enum('estado', ['Pendiente DC', 'Pendiente SA', 'Rechazado','Aprobado']);
            $table->string('motivo_rechazo')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('matricula_adicionals');
    }
};
