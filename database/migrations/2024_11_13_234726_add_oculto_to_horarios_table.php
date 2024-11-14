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
        Schema::table('horarios', function (Blueprint $table) {
            $table->boolean('oculto')->default(false)->after('vacantes');
        });
    }
    
    public function down()
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropColumn('oculto');
        });
    }
    
};
