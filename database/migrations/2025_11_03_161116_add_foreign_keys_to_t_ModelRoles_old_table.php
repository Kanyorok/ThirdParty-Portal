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
        Schema::table('t_ModelRoles_old', function (Blueprint $table) {
            $table->foreign(['BranchId'], 't_modelroles_branchid_foreign')->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'], 't_modelroles_createdby_foreign')->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'], 't_modelroles_deletedby_foreign')->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'], 't_modelroles_modifiedby_foreign')->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['role_id'], 't_modelroles_role_id_foreign')->references(['id'])->on('t_Roles')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ModelRoles_old', function (Blueprint $table) {
            $table->dropForeign('t_modelroles_branchid_foreign');
            $table->dropForeign('t_modelroles_createdby_foreign');
            $table->dropForeign('t_modelroles_deletedby_foreign');
            $table->dropForeign('t_modelroles_modifiedby_foreign');
            $table->dropForeign('t_modelroles_role_id_foreign');
        });
    }
};
