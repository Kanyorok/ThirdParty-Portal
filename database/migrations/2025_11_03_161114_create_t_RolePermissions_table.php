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
        Schema::create('t_RolePermissions', function (Blueprint $table) {
            $table->bigInteger('permission_id');
            $table->bigInteger('role_id');
            $table->bigInteger('CreatedBy')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();

            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RolePermissions');
    }
};
