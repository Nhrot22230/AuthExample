<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        /*Schema::create('carta_presentacion_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('horario_id');
            $table->unsignedBigInteger('especialidad_id'); // Añadir especialidad_id
            $table->string('estado')->default('Pendiente'); // Estado de la solicitud
            $table->text('motivo'); // Motivo de la solicitud (general)
            $table->text('motivo_rechazo')->nullable(); // Motivo de rechazo (nullable)
            $table->string('pdf_solicitud')->nullable(); // PDF generado para ser descargado
            $table->string('pdf_firmado')->nullable();   // PDF firmado por el Director
            $table->timestamps();

            // Relaciones
            $table->foreign('estudiante_id')->references('id')->on('estudiantes')->onDelete('cascade');
            $table->foreign('horario_id')->references('id')->on('horarios')->onDelete('cascade');
            $table->foreign('especialidad_id')->references('id')->on('especialidades')->onDelete('cascade'); // Relación con la especialidad
        });*/

        Schema::create('carta_presentacion_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->unsignedBigInteger('file_id')->nullable(); // Relación con la tabla files
            $table->string('estado')->default('Pendiente'); // Estado de la solicitud
            $table->text('motivo'); // Motivo general
            $table->text('motivo_rechazo')->nullable(); // Motivo de rechazo
            $table->timestamps();
        
            // Relaciones
            $table->foreign('file_id')->references('id')->on('files')->onDelete('set null'); // Eliminar archivo no afecta la solicitud
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('carta_presentacion_solicitudes');
    }
};