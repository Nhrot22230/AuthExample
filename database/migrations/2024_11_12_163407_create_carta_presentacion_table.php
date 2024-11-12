<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('carta_presentacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idEstudiante')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('idHorario')->constrained('horarios')->onDelete('cascade');
            $table->text('Motivo');
            $table->text('Observacion')->nullable();
            $table->string('ArchivoPDF')->nullable();
            $table->enum('Estado', ['Anulada', 'Pendiente', 'Atendida', 'Rechazado', 'En proceso']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carta_presentacion');
    }
};
