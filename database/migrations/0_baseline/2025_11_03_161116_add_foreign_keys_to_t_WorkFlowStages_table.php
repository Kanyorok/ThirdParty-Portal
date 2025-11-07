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
        Schema::table('t_WorkFlowStages', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PermissionId'])->references(['id'])->on('t_Permissions')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['StatusId'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['WorkFlowId'])->references(['Id'])->on('t_Workflows')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['WorkFlowLimitId'])->references(['Id'])->on('t_WorkFlowLimits')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['WorkFlowTypeId'])->references(['Id'])->on('t_WorkFlowTypes')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_WorkFlowStages', function (Blueprint $table) {
            $table->dropForeign('t_workflowstages_createdby_foreign');
            $table->dropForeign('t_workflowstages_deletedby_foreign');
            $table->dropForeign('t_workflowstages_modifiedby_foreign');
            $table->dropForeign('t_workflowstages_permissionid_foreign');
            $table->dropForeign('t_workflowstages_statusid_foreign');
            $table->dropForeign('t_workflowstages_workflowid_foreign');
            $table->dropForeign('t_workflowstages_workflowlimitid_foreign');
            $table->dropForeign('t_workflowstages_workflowtypeid_foreign');
        });
    }
};
