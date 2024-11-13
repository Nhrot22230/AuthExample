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
        Schema::create('pedido_cursos', function (Blueprint $table) {
            $table->id();
            $table->string('estado');
            $table->text('observaciones')->nullable();
            $table->boolean('enviado')->default(false);
            $table->foreignId('semestre_id')->constrained('semestres')->onDelete('cascade');
            $table->foreignId('facultad_id')->constrained('facultades')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->foreignId('plan_estudio_id')->constrained('plan_estudios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_cursos');
    }
};
