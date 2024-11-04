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
        Schema::create('respuesta_pregunta_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->foreignId('encuesta_pregunta_id')->constrained('encuesta_pregunta')->onDelete('cascade');
            $table->integer('cant1')->default(0);
            $table->integer('cant2')->default(0);
            $table->integer('cant3')->default(0);
            $table->integer('cant4')->default(0);
            $table->integer('cant5')->default(0);
            $table->timestamps();
        });
        Schema::create('respuesta_pregunta_jp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jp_horario_id')->constrained('jp_horario')->onDelete('cascade');
            $table->foreignId('encuesta_pregunta_id')->constrained('encuesta_pregunta')->onDelete('cascade');
            $table->integer('cant1')->default(0);
            $table->integer('cant2')->default(0);
            $table->integer('cant3')->default(0);
            $table->integer('cant4')->default(0);
            $table->integer('cant5')->default(0);
            $table->timestamps();
        });
        Schema::create('texto_respuesta_jp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jp_horario_id')->constrained('jp_horario')->onDelete('cascade');
            $table->foreignId('encuesta_pregunta_id')->constrained('encuesta_pregunta')->onDelete('cascade');
            $table->text('respuesta');
            $table->timestamps();
        });
        Schema::create('texto_respuesta_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->foreignId('encuesta_pregunta_id')->constrained('encuesta_pregunta')->onDelete('cascade');
            $table->text('respuesta');
            $table->timestamps();
        });
    }

    public function down(): void
    {

        Schema::dropIfExists('respuesta_pregunta_docente');
        Schema::dropIfExists('respuesta_pregunta_jp');
        Schema::dropIfExists('texto_respuesta_jp');
        Schema::dropIfExists('texto_respuesta_docente');
    }
};
