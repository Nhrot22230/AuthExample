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
        Schema::create('grupo_criterios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('obligatorio');
            $table->text('descripcion');
            $table->timestamps();
        });

        Schema::create('grupo_criterios_convocatoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_criterios_id')->constrained('grupo_criterios')->onDelete('cascade');
            $table->foreignId('convocatoria_id')->constrained('convocatoria')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_criterios_convocatoria');
        Schema::dropIfExists('grupo_criterios');
    }
};
