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
        Schema::table('t_ProcurementMethod', function (Blueprint $table) {
            $table->foreign(['ApprovedPlanId'])->references(['PlanID'])->on('t_ConsolidatedProcurementPlan')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ApprovedPlanLineId'])->references(['LineItemID'])->on('t_PlanLineItem')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ProcurementMethod', function (Blueprint $table) {
            $table->dropForeign('t_procurementmethod_approvedplanid_foreign');
            $table->dropForeign('t_procurementmethod_approvedplanlineid_foreign');
            $table->dropForeign('t_procurementmethod_createdby_foreign');
            $table->dropForeign('t_procurementmethod_deletedby_foreign');
            $table->dropForeign('t_procurementmethod_modifiedby_foreign');
        });
    }
};
