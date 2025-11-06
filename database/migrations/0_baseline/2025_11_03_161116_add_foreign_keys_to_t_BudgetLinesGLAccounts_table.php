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
        Schema::table('t_BudgetLinesGLAccounts', function (Blueprint $table) {
            $table->foreign(['BudgetGLAccountID'])->references(['BudgetGLID'])->on('t_BudgetGLMaster')->onUpdate('no action')->onDelete('no action');
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
        Schema::table('t_BudgetLinesGLAccounts', function (Blueprint $table) {
            $table->dropForeign('t_budgetlinesglaccounts_budgetglaccountid_foreign');
            $table->dropForeign('t_budgetlinesglaccounts_budgetlineid_foreign');
            $table->dropForeign('t_budgetlinesglaccounts_createdby_foreign');
            $table->dropForeign('t_budgetlinesglaccounts_deletedby_foreign');
            $table->dropForeign('t_budgetlinesglaccounts_modifiedby_foreign');
        });
    }
};
