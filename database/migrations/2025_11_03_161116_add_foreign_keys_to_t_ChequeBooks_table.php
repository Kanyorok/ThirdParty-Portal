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
        Schema::table('t_ChequeBooks', function (Blueprint $table) {
            $table->foreign(['BankAccountID'])->references(['AccountID'])->on('t_BankAccounts')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ChequeBooks', function (Blueprint $table) {
            $table->dropForeign('t_chequebooks_bankaccountid_foreign');
        });
    }
};
