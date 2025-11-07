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
        Schema::table('t_BudgetActivities', function (Blueprint $table) {
            $table->foreign(['ActivityID'])->references(['Id'])->on('t_BudgetActivityMaster')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BranchID'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BudgetID'])->references(['Id'])->on('t_Budgets')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BudgetLineID'])->references(['Id'])->on('t_BudgetLines')->onUpdate('no action')->onDelete('no action');
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
        Schema::table('t_BudgetActivities', function (Blueprint $table) {
            $table->dropForeign('t_budgetactivities_activityid_foreign');
            $table->dropForeign('t_budgetactivities_branchid_foreign');
            $table->dropForeign('t_budgetactivities_budgetid_foreign');
            $table->dropForeign('t_budgetactivities_budgetlineid_foreign');
            $table->dropForeign('t_budgetactivities_createdby_foreign');
            $table->dropForeign('t_budgetactivities_deletedby_foreign');
            $table->dropForeign('t_budgetactivities_modifiedby_foreign');
        });
    }
};
