<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_BankTransactions', function (Blueprint $table) {
            $table->id('BankTxnID');

            $table->unsignedBigInteger('BankAccountID');
            $table->unsignedBigInteger('TransactionTypeID'); // FK -> t_FinanceTransactionTypes.Id

            $table->date('DocDate');
            $table->unsignedBigInteger('CurrencyID');
            $table->decimal('ExchangeRate', 18, 6)->default(1);

            $table->decimal('Amount', 18, 2);
            $table->decimal('AmountBase', 18, 2)->nullable();

            $table->string('Reference', 100)->nullable();
            $table->string('Narration', 300)->nullable();

            $table->string('Status', 20)->default('Draft'); // Draft|Posted|Voided
            $table->boolean('IsActive')->default(1);

            // Audit (SQL Server style)
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            // FKs
            $table->foreign('BankAccountID')->references('AccountID')->on('t_BankAccounts');
            $table->foreign('TransactionTypeID')->references('Id')->on('t_FinanceTransactionTypes');
            $table->foreign('CurrencyID')->references('Id')->on('t_Currencies');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_BankTransactions');
    }
};
