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
        Schema::table('t_BudgetDriverProjections', function (Blueprint $table) {
            $table->foreign(['BudgetID'])->references(['Id'])->on('t_Budgets')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CurrencyID'])->references(['Id'])->on('t_Currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetDriverProjections', function (Blueprint $table) {
            $table->dropForeign('t_budgetdriverprojections_budgetid_foreign');
            $table->dropForeign('t_budgetdriverprojections_createdby_foreign');
            $table->dropForeign('t_budgetdriverprojections_currencyid_foreign');
            $table->dropForeign('t_budgetdriverprojections_deletedby_foreign');
            $table->dropForeign('t_budgetdriverprojections_modifiedby_foreign');
        });
    }
};
