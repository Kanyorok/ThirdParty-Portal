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
        Schema::table('t_BudgetLineLedgerLimits', function (Blueprint $table) {
            $table->foreign(['BranchID'], 'FK_BudgetLineLedgerLimits_Branches')->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BudgetLineID'], 'FK_BudgetLineLedgerLimits_BudgetLines')->references(['Id'])->on('t_BudgetLines')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetLineLedgerLimits', function (Blueprint $table) {
            $table->dropForeign('FK_BudgetLineLedgerLimits_Branches');
            $table->dropForeign('FK_BudgetLineLedgerLimits_BudgetLines');
        });
    }
};
