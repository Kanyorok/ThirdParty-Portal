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
        Schema::table('t_PlanLineItem', function (Blueprint $table) {
            $table->foreign(['BranchID'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CategoryID'])->references(['Id'])->on('t_ItemCategories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DepartmentID'])->references(['Id'])->on('t_Departments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemID'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PlanID'])->references(['PlanID'])->on('t_ConsolidatedProcurementPlan')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PlanLineItem', function (Blueprint $table) {
            $table->dropForeign('t_planlineitem_branchid_foreign');
            $table->dropForeign('t_planlineitem_categoryid_foreign');
            $table->dropForeign('t_planlineitem_createdby_foreign');
            $table->dropForeign('t_planlineitem_deletedby_foreign');
            $table->dropForeign('t_planlineitem_departmentid_foreign');
            $table->dropForeign('t_planlineitem_itemid_foreign');
            $table->dropForeign('t_planlineitem_modifiedby_foreign');
            $table->dropForeign('t_planlineitem_planid_foreign');
        });
    }
};
