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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades')->onDelete('set null');
            $table->foreignId('seccion_Id')->nullable()->constrained('secciones')->onDelete('set null');
            $table->string('cod_curso');
            $table->string('nombre');
            $table->double('creditos');
            $table->string('estado')->default('inactivo');
            $table->double('ct')->default(0);
            $table->double('pa')->default(0);
            $table->double('pb')->default(0);
            $table->integer('me')->default(0);
            $table->timestamps();
        });

        Schema::create('docente_curso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')->constrained('docentes')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docente_curso');
        Schema::dropIfExists('cursos');
    }
};
