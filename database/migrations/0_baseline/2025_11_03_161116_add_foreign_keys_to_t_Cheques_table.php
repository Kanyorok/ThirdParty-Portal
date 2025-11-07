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
        Schema::table('t_Cheques', function (Blueprint $table) {
            $table->foreign(['BankAccountID'])->references(['AccountID'])->on('t_BankAccounts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ChequeBookID'])->references(['ChequeBookID'])->on('t_ChequeBooks')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CurrencyID'])->references(['Id'])->on('t_Currencies')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Cheques', function (Blueprint $table) {
            $table->dropForeign('t_cheques_bankaccountid_foreign');
            $table->dropForeign('t_cheques_chequebookid_foreign');
            $table->dropForeign('t_cheques_currencyid_foreign');
        });
    }
};
