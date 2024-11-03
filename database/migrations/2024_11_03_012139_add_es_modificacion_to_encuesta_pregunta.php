<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('encuesta_pregunta', function (Blueprint $table) {
            $table->boolean('es_modificacion')->default(false)->after('pregunta_id');
        });
    }


    public function down(): void
    {
        Schema::table('encuesta_pregunta', function (Blueprint $table) {
            $table->dropColumn('es_modificacion');
        });
    }
};
