<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('horario_actividades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('horario_id');  // Relación con la tabla Horario
            $table->string('actividad');               // Nombre de la actividad
            $table->integer('duracion_semanas');       // Duración en semanas
            $table->integer('semana_ocurre');          // Semana en la que ocurre la actividad
            $table->timestamps();

            // Relación con Horario
            $table->foreign('horario_id')->references('id')->on('horarios')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('horario_actividades');
    }
};