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
        Schema::create('tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('resumen');
            $table->enum('estado', ['pendiente', 'aceptado', 'rechazado'])->default('pendiente');
            $table->foreignId('file_id')->nullable()->constrained('files');
            $table->foreignId('file_firmado_id')->nullable()->constrained('files');
            $table->foreignId('area_id')->constrained('areas');
            $table->timestamps();
        });

        Schema::create('autores_tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_tesis');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->timestamps();
        });
        
        Schema::create('asesores_tema_tesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_tesis_id')->constrained('tema_tesis');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autores_tema_tesis');
        Schema::dropIfExists('asesores_tema_tesis');
        Schema::dropIfExists('tema_tesis');
    }
};
