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
        Schema::table('t_WorkFlowEscalation', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SupervisorId'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UserId'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['WorkFlowStageId'])->references(['Id'])->on('t_WorkFlowStages')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_WorkFlowEscalation', function (Blueprint $table) {
            $table->dropForeign('t_workflowescalation_createdby_foreign');
            $table->dropForeign('t_workflowescalation_deletedby_foreign');
            $table->dropForeign('t_workflowescalation_modifiedby_foreign');
            $table->dropForeign('t_workflowescalation_supervisorid_foreign');
            $table->dropForeign('t_workflowescalation_userid_foreign');
            $table->dropForeign('t_workflowescalation_workflowstageid_foreign');
        });
    }
};
