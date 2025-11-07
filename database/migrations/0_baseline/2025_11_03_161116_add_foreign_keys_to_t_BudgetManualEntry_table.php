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
        Schema::table('t_BudgetManualEntry', function (Blueprint $table) {
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
        Schema::table('t_BudgetManualEntry', function (Blueprint $table) {
            $table->dropForeign('t_budgetmanualentry_branchid_foreign');
            $table->dropForeign('t_budgetmanualentry_budgetid_foreign');
            $table->dropForeign('t_budgetmanualentry_budgetlineid_foreign');
            $table->dropForeign('t_budgetmanualentry_createdby_foreign');
            $table->dropForeign('t_budgetmanualentry_deletedby_foreign');
            $table->dropForeign('t_budgetmanualentry_modifiedby_foreign');
        });
    }
};
