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
            $table->string('name')->unique();
            $table->string('entity_type');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('permission_categories')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('role_scopes', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('scope_id')->constrained('scopes')->onDelete('cascade');

            $table->primary(['role_id', 'scope_id']);
        });

        Schema::create('role_scope_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('scope_id')->constrained('scopes')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->unsignedBigInteger('entity_id'); // ID de la entidad específica
            $table->string('entity_type'); // Tipo de entidad (clase)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_scope_usuarios');
        Schema::dropIfExists('role_scopes');
        Schema::dropIfExists('scopes');
    }
};
