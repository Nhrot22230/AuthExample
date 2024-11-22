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
        Schema::create('procesos_aprobacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('fases_aprobadas')->default(0);
            $table->unsignedSmallInteger('total_fases')->default(3);
            $table->string('titulo');
            $table->string('resumen');
            $table->foreignId('file_id')->nullable()->constrained('files');
            $table->foreignId('tema_tesis_id')->constrained('tema_tesis');
            $table->enum('estado_proceso', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos_aprobacion');
    }
};
