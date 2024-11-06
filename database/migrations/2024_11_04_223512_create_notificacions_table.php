<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id');  // ID del estudiante
            $table->unsignedBigInteger('especialidad_id'); // ID de la especialidad
            $table->string('mensaje');  // Mensaje de la notificación
            $table->boolean('leida')->default(false);  // Estado de lectura
            $table->timestamps();

            // Claves foráneas
            $table->foreign('estudiante_id')->references('id')->on('estudiantes')->onDelete('cascade');
            $table->foreign('especialidad_id')->references('id')->on('especialidades')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones'); // Asegúrate de que la tabla sea `notificaciones`
    }
};
