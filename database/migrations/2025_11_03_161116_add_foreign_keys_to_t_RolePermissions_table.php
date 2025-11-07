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
        Schema::table('t_RolePermissions', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['permission_id'])->references(['id'])->on('t_Permissions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['role_id'])->references(['id'])->on('t_Roles')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RolePermissions', function (Blueprint $table) {
            $table->dropForeign('t_rolepermissions_createdby_foreign');
            $table->dropForeign('t_rolepermissions_modifiedby_foreign');
            $table->dropForeign('t_rolepermissions_permission_id_foreign');
            $table->dropForeign('t_rolepermissions_role_id_foreign');
        });
    }
};
