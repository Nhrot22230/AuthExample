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
        //
        /*Schema::create('respuesta_pregunta', function (Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('encuesta_pregunta')->onDelete('cascade');
            $table->integer('cant1')->default(0);
            $table->integer('cant2')->default(0);
            $table->integer('cant3')->default(0);
            $table->integer('cant4')->default(0);
            $table->integer('cant5')->default(0);
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->foreignId('jp_horario_id')->constrained('jp_horario')->onDelete('cascade');
            $table->timestamps();
        });*/

        Schema::create('respuesta_pregunta_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade'); // Relación con Horarios
            $table->foreignId('encuesta_pregunta_id')->constrained('encuesta_pregunta')->onDelete('cascade'); // Relación con EncuestaPregunta
            $table->integer('cant1')->default(0);
            $table->integer('cant2')->default(0);
            $table->integer('cant3')->default(0);
            $table->integer('cant4')->default(0);
            $table->integer('cant5')->default(0);
            $table->timestamps();
        });

        /*Schema::create('texto_respuesta_pregunta', function (Blueprint $table) {
            $table->id();
            $table->string('texto_respuesta');
            $table->foreignId('encuesta_pregunta_id')->constrained('encuesta_pregunta')->onDelete('cascade'); // Relación con EncuestaPregunta
            $table->timestamps();
        });*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('respuesta_pregunta_docente');
        //Schema::dropIfExists('respuesta_pregunta');
    }
};
