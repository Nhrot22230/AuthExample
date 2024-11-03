<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->unique();
            $table->string('codigoAdministrativo')->unique();
            $table->string('cargo');
            $table->string('lugarTrabajo');
            $table->foreignId('facultad_id')->nullable()->constrained('facultades')->nullOnDelete(); // Relación opcional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrativos');
    }
};
