<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('permission_scope', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->foreignId('scope_id')->constrained('scopes')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['permission_id', 'scope_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('permission_scope');
    }
};
