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
        Schema::table('pedido_curso_electivo', function (Blueprint $table) {
            $table->string('nivel')->nullable(); // Columna para almacenar el nivel
            $table->integer('creditosReq')->nullable(); // Columna para almacenar los créditos requeridos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_curso_electivo', function (Blueprint $table) {
            $table->dropColumn('nivel');
            $table->dropColumn('creditosReq');
        });
    }
};
