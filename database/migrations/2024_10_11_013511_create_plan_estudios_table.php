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
            $table->string('codigo', 10)->unique();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });

        Schema::create('plan_estudio_semestre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_estudio_id')->constrained();
            $table->foreignId('semestre_id')->constrained();
            $table->timestamps();
        });

        Schema::create('plan_estudio_curso', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nivel');
            $table->foreignId('curso_id')->constrained();
            $table->foreignId('plan_estudio_id')->constrained();
            $table->timestamps();
        });

        Schema::create('curso_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_estudio_id')->constrained('plan_estudios')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('requisito_id')->constrained('cursos')->onDelete('cascade');
            $table->string('tipo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_requisito');
        Schema::dropIfExists('plan_estudio_semestre');
        Schema::dropIfExists('plan_estudio_curso');
        Schema::dropIfExists('plan_estudios');
    }
};
