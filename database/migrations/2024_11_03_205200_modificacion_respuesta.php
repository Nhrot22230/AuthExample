<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('texto_respuesta_jp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jp_horario_id')->constrained('jp_horario')->onDelete('cascade');
            $table->foreignId('encuesta_pregunta_id')->constrained('encuesta_pregunta')->onDelete('cascade');
            $table->longText('respuesta');
            $table->timestamps();
        });

        Schema::create('texto_respuesta_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->foreignId('encuesta_pregunta_id')->constrained('encuesta_pregunta')->onDelete('cascade');
            $table->longText('respuesta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('texto_respuesta_jp');
        Schema::dropIfExists('texto_respuesta_docente');
    }
};
