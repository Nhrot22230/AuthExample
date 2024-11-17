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
        Schema::create('comite_candidato_convocatoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade'); // Relación con docente
            $table->foreignId('candidato_id')->constrained('usuarios')->onDelete('cascade'); // Relación con candidato (usuario)
            $table->foreignId('convocatoria_id')->constrained('convocatoria')->onDelete('cascade'); // Relación con convocatoria
            $table->enum('estado', ['pendiente cv', 'desaprobado cv', 'aprobado cv', 'culminado entrevista', 
                         'desaprobado entrevista', 'aprobado entrevista'])->default('pendiente cv');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comite_candidato_convocatoria');
    }
};
