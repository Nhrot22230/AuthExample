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
        Schema::create('scopes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // Puede ser 'facultad', 'departamento', 'sección', 'especialidad', 'curso'
            $table->unsignedBigInteger('related_id'); // ID del modelo relacionado (e.g., facultad_id, departamento_id)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scopes');
    }
};
