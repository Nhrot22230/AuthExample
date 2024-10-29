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
        Schema::dropIfExists('docente_horario');
        Schema::dropIfExists('estudiante_horario_jp');
        Schema::dropIfExists('estudiante_horario');
        Schema::dropIfExists('jp_horario');
        Schema::dropIfExists('horarios');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
