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
        Schema::create('curso_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_estudio_id')->constrained('plan_estudios')->onDelete('cascade');
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->foreignId('requisito_id')->constrained('cursos')->onDelete('cascade');
            $table->string('tipo'); // Ej: "pre-requisito", "co-requisito", etc.
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitos');
    }
};
