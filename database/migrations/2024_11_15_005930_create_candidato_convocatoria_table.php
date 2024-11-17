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
        Schema::create('candidato_convocatoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convocatoria_id')->constrained('convocatoria')->onDelete('cascade'); // Relación con convocatoria
            $table->foreignId('candidato_id')->constrained('usuarios')->onDelete('cascade'); // Relación con usuario (candidato)
            $table->enum('estadoFinal', ['pendiente cv', 'desaprobado cv', 'aprobado cv', 'culminado entrevista', 
                         'desaprobado entrevista', 'aprobado entrevista'])->default('pendiente cv');
            $table->string('urlCV');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidato_convocatoria');
    }
};
