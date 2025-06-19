<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_BudgetLineLink', function (Blueprint $table) {
            $table->dropForeign('t_budgetlinelink_budgetlineid_foreign'); // exact name of the FK
        });
    }

    public function down(): void
    {
        Schema::table('t_BudgetLineLink', function (Blueprint $table) {
            $table->foreign('BudgetLineID')->references('BudgetLineID')->on('t_BudgetMaster');
        });
    }
};
