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
        Schema::table('t_BudgetReallocationLog', function (Blueprint $table) {
            $table->foreign(['ActivityID'])->references(['Id'])->on('t_BudgetActivityMaster')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BudgetLineID'])->references(['Id'])->on('t_BudgetLines')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LimitID'])->references(['Id'])->on('t_BudgetLineLedgerLimits')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ReallocationID'])->references(['id'])->on('t_BudgetReallocations')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetReallocationLog', function (Blueprint $table) {
            $table->dropForeign('t_budgetreallocationlog_activityid_foreign');
            $table->dropForeign('t_budgetreallocationlog_budgetlineid_foreign');
            $table->dropForeign('t_budgetreallocationlog_limitid_foreign');
            $table->dropForeign('t_budgetreallocationlog_reallocationid_foreign');
        });
    }
};
