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
        Schema::create('semestres', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('anho');
            $table->unsignedInteger('periodo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado')->default('inactivo');
            $table->unique(['anho', 'periodo']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semestres');
    }
};
