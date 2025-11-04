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
        Schema::table('t_BankTransfers', function (Blueprint $table) {
            $table->foreign(['CurrencyID'])->references(['Id'])->on('t_Currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FromBankAccountID'])->references(['AccountID'])->on('t_BankAccounts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ToBankAccountID'])->references(['AccountID'])->on('t_BankAccounts')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BankTransfers', function (Blueprint $table) {
            $table->dropForeign('t_banktransfers_currencyid_foreign');
            $table->dropForeign('t_banktransfers_frombankaccountid_foreign');
            $table->dropForeign('t_banktransfers_tobankaccountid_foreign');
        });
    }
};
