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
        Schema::table('t_BudgetLineLink', function (Blueprint $table) {
            $table->foreign(['BudgetLineID'])->references(['BudgetLineID'])->on('t_BudgetMaster')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LineItemID'])->references(['LineItemID'])->on('t_PlanLineItem')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['LinkedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetLineLink', function (Blueprint $table) {
            $table->dropForeign('t_budgetlinelink_budgetlineid_foreign');
            $table->dropForeign('t_budgetlinelink_createdby_foreign');
            $table->dropForeign('t_budgetlinelink_deletedby_foreign');
            $table->dropForeign('t_budgetlinelink_lineitemid_foreign');
            $table->dropForeign('t_budgetlinelink_linkedby_foreign');
            $table->dropForeign('t_budgetlinelink_modifiedby_foreign');
        });
    }
};
