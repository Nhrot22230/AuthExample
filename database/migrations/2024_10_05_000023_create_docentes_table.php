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
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained()->unique();
            $table->string('codigoDocente')->unique();
            $table->enum('tipo', ['TPA', 'TC', 'TCP']);
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->foreignId('seccion_id')->nullable()->constrained('secciones');
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
