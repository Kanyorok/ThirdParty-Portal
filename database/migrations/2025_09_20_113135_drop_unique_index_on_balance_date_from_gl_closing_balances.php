<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_GLClosingBalances', function (Blueprint $table) {
            // Drop the wrong unique index (with space in the name)
            $table->dropUnique('t_glclosingbalances_balancedate _unique');
        });

        Schema::table('t_GLClosingBalances', function (Blueprint $table) {
            // Rename column to remove trailing space
            $table->renameColumn('BalanceDate ', 'BalanceDate');
        });

        Schema::table('t_GLClosingBalances', function (Blueprint $table) {
            // Ensure it's a normal datetime column (not unique)
            $table->dateTime('BalanceDate')->change();
        });
    }

    public function down(): void
    {
        Schema::table('t_GLClosingBalances', function (Blueprint $table) {
            // Revert back: add unique index on BalanceDate
            $table->unique('BalanceDate', 't_glclosingbalances_balancedate _unique');
        });

        Schema::table('t_GLClosingBalances', function (Blueprint $table) {
            // Rename column back with trailing space (not recommended, but for rollback)
            $table->renameColumn('BalanceDate', 'BalanceDate ');
        });
    }
};
