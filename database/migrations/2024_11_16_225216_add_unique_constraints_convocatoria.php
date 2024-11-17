<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Para la tabla comite_candidato_convocatoria
        Schema::table('comite_candidato_convocatoria', function (Blueprint $table) {
            $table->unique(['docente_id', 'candidato_id', 'convocatoria_id'], 'comite_docente_candidato_convocatoria_unique');
        });

        // Para la tabla candidato_convocatoria
        Schema::table('candidato_convocatoria', function (Blueprint $table) {
            $table->unique(['convocatoria_id', 'candidato_id'], 'candidato_convocatoria_unique');
        });

        // Para la tabla docente_convocatoria
        Schema::table('docente_convocatoria', function (Blueprint $table) {
            $table->unique(['convocatoria_id', 'docente_id'], 'docente_convocatoria_unique');
        });

        // Para la tabla grupo_criterios_convocatoria
        Schema::table('grupo_criterios_convocatoria', function (Blueprint $table) {
            $table->unique(['convocatoria_id', 'grupo_criterios_id'], 'grupo_criterios_convocatoria_unique');
        });
    }

    public function down()
    {
        // Eliminar las restricciones en caso de revertir la migración
        Schema::table('comite_candidato_convocatoria', function (Blueprint $table) {
            $table->dropUnique(['docente_id', 'candidato_id', 'convocatoria_id']);
        });

        Schema::table('candidato_convocatoria', function (Blueprint $table) {
            $table->dropUnique(['convocatoria_id', 'candidato_id']);
        });

        Schema::table('docente_convocatoria', function (Blueprint $table) {
            $table->dropUnique(['convocatoria_id', 'docente_id']);
        });

        Schema::table('grupo_criterios_convocatoria', function (Blueprint $table) {
            $table->dropUnique(['convocatoria_id', 'grupo_criterios_id']);
        });
    }
};
