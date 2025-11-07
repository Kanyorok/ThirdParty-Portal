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
        Schema::table('t_SchedulePlan', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PlanId'])->references(['PlanID'])->on('t_ConsolidatedProcurementPlan')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PlanLineId'])->references(['LineItemID'])->on('t_PlanLineItem')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_SchedulePlan', function (Blueprint $table) {
            $table->dropForeign('t_scheduleplan_createdby_foreign');
            $table->dropForeign('t_scheduleplan_deletedby_foreign');
            $table->dropForeign('t_scheduleplan_modifiedby_foreign');
            $table->dropForeign('t_scheduleplan_planid_foreign');
            $table->dropForeign('t_scheduleplan_planlineid_foreign');
        });
    }
};
