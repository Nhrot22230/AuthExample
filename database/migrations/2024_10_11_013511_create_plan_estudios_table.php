<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_estudios', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad_semestres')->default(10);
            $table->foreignId('especialidad_id')->constrained('especialidades');
            $table->enum('estado', ['activo', 'inactivo'])->default('inactivo');
            $table->timestamps();
        });

        Schema::create('plan_estudio_semestre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_estudio_id')->constrained('plan_estudios')->onDelete('cascade');
            $table->foreignId('semestre_id')->constrained('semestres')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('plan_estudio_curso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_estudio_id')->constrained('plan_estudios')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->string('nivel', 3)->default('0');
            $table->double('creditosReq')->default(0);
            $table->timestamps();
        });

        Schema::create('requisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos');
            $table->foreignId('plan_estudio_id')->constrained('plan_estudios');
            $table->foreignId('curso_requisito_id')->constrained('cursos');
            $table->enum('tipo', ['simultaneo', 'llevado']);
            $table->double('notaMinima')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitos');
        Schema::dropIfExists('plan_estudio_curso');
        Schema::dropIfExists('plan_estudio_semestre');
        Schema::dropIfExists('plan_estudios');
    }
};
