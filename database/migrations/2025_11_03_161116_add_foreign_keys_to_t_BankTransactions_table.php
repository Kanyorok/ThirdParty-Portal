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
        Schema::table('t_BankTransactions', function (Blueprint $table) {
            $table->foreign(['BankAccountID'])->references(['AccountID'])->on('t_BankAccounts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CurrencyID'])->references(['Id'])->on('t_Currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TransactionTypeID'])->references(['Id'])->on('t_FinanceTransactionTypes')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BankTransactions', function (Blueprint $table) {
            $table->dropForeign('t_banktransactions_bankaccountid_foreign');
            $table->dropForeign('t_banktransactions_currencyid_foreign');
            $table->dropForeign('t_banktransactions_transactiontypeid_foreign');
        });
    }
};
