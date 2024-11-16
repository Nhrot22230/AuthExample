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
        Schema::create('convocatoria', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamp('fechaEntrevista');
            $table->timestamp('fechaInicio');
            $table->timestamp('fechaFin');
            $table->enum('estado', ['abierta', 'cerrada', 'cancelada'])->default('abierta');
            $table->foreignId('seccion_id')->constrained('secciones')->onDelete('cascade'); // Relación con Sección
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convocatoria');
    }
};
