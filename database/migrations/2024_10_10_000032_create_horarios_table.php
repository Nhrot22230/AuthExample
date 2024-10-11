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
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('semestre_id')->constrained('semestres')->onDelete('cascade');
            $table->string('nombre');
            $table->string('codigo');
            $table->unsignedInteger('vacantes');
            $table->timestamps();
        });


        Schema::create('jp_horario', function (Blueprint $table) {
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            
            $table->primary(['horario_id', 'usuario_id']);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jp_horario');
        Schema::dropIfExists('horarios');
    }
};