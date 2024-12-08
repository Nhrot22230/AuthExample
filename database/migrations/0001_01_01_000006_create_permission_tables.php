<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = config('permission.teams'); // si se usan equipos
        $tableNames = config('permission.table_names'); // nombres personalizados de tablas
        $columnNames = config('permission.column_names'); // nombres personalizados de columnas
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        // Creación de la tabla de permisos
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id'); // permission id
            $table->string('name');       // nombre del permiso
            $table->string('guard_name'); // nombre del guard (para diferentes tipos de autenticación)
            $table->foreignId('scope_id')->constrained('scopes')->onDelete('cascade'); // relaciona el permiso con un scope
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        // Creación de la tabla de roles
        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id');
            if ($teams || config('permission.testing')) { // permiso para equipos
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('guard_name'); // Se agregó la columna 'guard_name' aquí
            $table->foreignId('scope_id')->constrained('scopes')->onDelete('cascade'); // un rol tiene un solo scope
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        // Creación de la tabla de relación entre roles y permisos
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->foreignId($pivotRole)->constrained($tableNames['roles'])->onDelete('cascade');
            $table->foreignId($pivotPermission)->constrained($tableNames['permissions'])->onDelete('cascade');
            $table->timestamps();
            $table->unique([$pivotRole, $pivotPermission]);
        });

        // Creación de la tabla de relación entre modelos y roles
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type']);
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type']);
            }
        });

        app('cache')->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['permissions']);
        Schema::dropIfExists($tableNames['roles']);
    }
};
