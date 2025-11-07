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
        Schema::table('t_BudgetMonthlyProjectionsAllocation', function (Blueprint $table) {
            $table->foreign(['BudgetID'])->references(['Id'])->on('t_Budgets')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BudgetProjectionID'])->references(['Id'])->on('t_BudgetDriverProjections')->onUpdate('no action')->onDelete('no action');
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
        Schema::table('t_BudgetMonthlyProjectionsAllocation', function (Blueprint $table) {
            $table->dropForeign('t_budgetmonthlyprojectionsallocation_budgetid_foreign');
            $table->dropForeign('t_budgetmonthlyprojectionsallocation_budgetprojectionid_foreign');
            $table->dropForeign('t_budgetmonthlyprojectionsallocation_createdby_foreign');
            $table->dropForeign('t_budgetmonthlyprojectionsallocation_deletedby_foreign');
            $table->dropForeign('t_budgetmonthlyprojectionsallocation_modifiedby_foreign');
        });
    }
};
