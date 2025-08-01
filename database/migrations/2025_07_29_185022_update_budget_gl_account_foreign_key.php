<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_BudgetLinesGLAccounts', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['BudgetGLAccountID']);

            // Add new foreign key constraint to t_BudgetGLMaster
            $table->foreign('BudgetGLAccountID')
                ->references('BudgetGLID')
                ->on('t_BudgetGLMaster');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetLinesGLAccounts', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['BudgetGLAccountID']);

            // Restore the original foreign key to t_BudgetGLAccounts
            $table->foreign('BudgetGLAccountID')
                ->references('Id')
                ->on('t_BudgetGLAccounts');
        });
    }
};
