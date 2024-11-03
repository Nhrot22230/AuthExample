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
        Schema::create('informes_riesgo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('codigo_alumno_riesgo');
            $table->date('fecha')->nullable();
            $table->integer('semana')->nullable();
            $table->string('desempenho')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado')->nullable();
            $table->string('nombre_profesor')->nullable();
            $table->timestamps();

            $table->foreign('codigo_alumno_riesgo')->references('id')->on('estudiante_riesgo')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes_riesgo');
    }
};
