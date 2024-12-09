<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('scopes', function (Blueprint $table) {
            $table->string('access_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('scopes', function (Blueprint $table) {
            $table->dropColumn('access_path');
        });
    }
};
