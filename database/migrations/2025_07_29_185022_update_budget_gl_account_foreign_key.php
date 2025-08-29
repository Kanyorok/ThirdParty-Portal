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
        Schema::table('t_BudgetLinesGLAccounts', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('BudgetGLAccountID');
        });

        Schema::table('t_BudgetLinesGLAccounts', static function (Blueprint $table) {
            $table->foreignId('BudgetGLAccountID')->nullable()
                ->references('BudgetGLID')
                ->on('t_BudgetGLMaster');
        });

        /* @todo martin collum nullable
         * Schema::table('t_BudgetLinesGLAccounts', static function (Blueprint $table) {
         * $table->bigInteger('BudgetGLAccountID')->nullable(false)->change();
         * });*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetLinesGLAccounts', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('BudgetGLAccountID');
        });

        Schema::table('t_BudgetLinesGLAccounts', static function (Blueprint $table) {
            $table->foreignId('BudgetGLAccountID')
                ->references('BudgetGLID')
                ->on('t_BudgetGLAccounts');
        });
    }
};
