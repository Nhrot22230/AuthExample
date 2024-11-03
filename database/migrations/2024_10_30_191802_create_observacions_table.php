<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('responsable_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('tema_tesis_id')->constrained('tema_de_tesis')->onDelete('cascade');
            $table->string('descripcion');
            $table->enum('estado', ['aprobado', 'pendiente', 'desaprobado'])->default('pendiente');
            $table->date('fecha');
            $table->string('archivo')->nullable(); // Campo para archivo, actualmente vacío
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observaciones');
    }
};
