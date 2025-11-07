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
        Schema::table('t_BudgetTopDown', function (Blueprint $table) {
            $table->foreign(['BudgetLineID'])->references(['Id'])->on('t_BudgetLines')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PeriodID'])->references(['Id'])->on('t_BudgetPeriods')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ScenarioID'])->references(['Id'])->on('t_BudgetScenarioPlanning')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetTopDown', function (Blueprint $table) {
            $table->dropForeign('t_budgettopdown_budgetlineid_foreign');
            $table->dropForeign('t_budgettopdown_createdby_foreign');
            $table->dropForeign('t_budgettopdown_deletedby_foreign');
            $table->dropForeign('t_budgettopdown_modifiedby_foreign');
            $table->dropForeign('t_budgettopdown_periodid_foreign');
            $table->dropForeign('t_budgettopdown_scenarioid_foreign');
        });
    }
};
