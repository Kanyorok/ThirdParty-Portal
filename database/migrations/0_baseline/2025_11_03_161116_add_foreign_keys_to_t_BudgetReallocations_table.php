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
        Schema::table('t_BudgetReallocations', function (Blueprint $table) {
            $table->foreign(['ApprovedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BranchID'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BudgetID'])->references(['Id'])->on('t_Budgets')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DepartmentID'])->references(['Id'])->on('t_Departments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FromActivityID'])->references(['Id'])->on('t_BudgetActivityMaster')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FromBudgetLineID'])->references(['Id'])->on('t_BudgetLines')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ToActivityID'])->references(['Id'])->on('t_BudgetActivityMaster')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ToBudgetLineID'])->references(['Id'])->on('t_BudgetLines')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetReallocations', function (Blueprint $table) {
            $table->dropForeign('t_budgetreallocations_approvedby_foreign');
            $table->dropForeign('t_budgetreallocations_branchid_foreign');
            $table->dropForeign('t_budgetreallocations_budgetid_foreign');
            $table->dropForeign('t_budgetreallocations_createdby_foreign');
            $table->dropForeign('t_budgetreallocations_deletedby_foreign');
            $table->dropForeign('t_budgetreallocations_departmentid_foreign');
            $table->dropForeign('t_budgetreallocations_fromactivityid_foreign');
            $table->dropForeign('t_budgetreallocations_frombudgetlineid_foreign');
            $table->dropForeign('t_budgetreallocations_modifiedby_foreign');
            $table->dropForeign('t_budgetreallocations_toactivityid_foreign');
            $table->dropForeign('t_budgetreallocations_tobudgetlineid_foreign');
        });
    }
};
