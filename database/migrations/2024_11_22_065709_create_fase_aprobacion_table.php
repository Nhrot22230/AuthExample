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
        Schema::create('fases_aprobacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('fase');
            $table->text('observacion')->nullable();
            $table->enum('estado_fase', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->foreignId('proceso_aprobacion_id')->constrained('procesos_aprobacion');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->foreignId('file_id')->nullable()->constrained('files');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fases_aprobacion');
    }
};
