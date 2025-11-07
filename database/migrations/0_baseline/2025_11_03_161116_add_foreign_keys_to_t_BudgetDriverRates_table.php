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
        Schema::table('t_BudgetDriverRates', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PeriodTypeID'])->references(['Id'])->on('t_BudgetPeriodTypes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ProductTypeId'])->references(['Id'])->on('t_BudgetProducts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RateTypeID'])->references(['Id'])->on('t_BudgetRates')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetDriverRates', function (Blueprint $table) {
            $table->dropForeign('t_budgetdriverrates_createdby_foreign');
            $table->dropForeign('t_budgetdriverrates_deletedby_foreign');
            $table->dropForeign('t_budgetdriverrates_modifiedby_foreign');
            $table->dropForeign('t_budgetdriverrates_periodtypeid_foreign');
            $table->dropForeign('t_budgetdriverrates_producttypeid_foreign');
            $table->dropForeign('t_budgetdriverrates_ratetypeid_foreign');
        });
    }
};
