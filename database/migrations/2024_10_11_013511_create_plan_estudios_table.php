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
        Schema::create('plan_estudios', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['activo', 'inactivo'])->default('inactivo');
            $table->timestamps();
        });

        Schema::create('plan_estudio_semestre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_estudio_id')->constrained();
            $table->foreignId('semestre_id')->constrained();
            $table->timestamps();
        });

        Schema::create('requisitos', function (Blueprint $table) {
            $table->id();
            $table->integer('nivel')->default(0); // 0 para los electivos 1+ para los cursos de la carrera
            $table->foreignId('curso_id')->constrained();
            $table->foreignId('plan_estudio_id')->constrained();
            $table->foreignId('curso_requisito_id')->constrained('cursos')->nullable();
            $table->string('tipo', 10)->nullable(); // tipo de requisito (llevar simultaneo, haber llevado, haber aprobado, cantidad de creditos)
            $table->double('notaMinima')->default(0)->nullable();
            $table->double('cantCreditos')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitos');
        Schema::dropIfExists('plan_estudio_semestre');
        Schema::dropIfExists('plan_estudios');
    }
};
