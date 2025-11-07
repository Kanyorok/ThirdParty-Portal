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
        Schema::table('t_BankAccounts', function (Blueprint $table) {
            $table->foreign(['BankID'])->references(['BankID'])->on('t_Banks')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BranchID'])->references(['BranchID'])->on('t_BankBranches')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BankAccounts', function (Blueprint $table) {
            $table->dropForeign('t_bankaccounts_bankid_foreign');
            $table->dropForeign('t_bankaccounts_branchid_foreign');
        });
    }
};
