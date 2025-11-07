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
        Schema::create('t_BankTransactions', function (Blueprint $table) {
            $table->bigIncrements('BankTxnID');
            $table->bigInteger('BankAccountID');
            $table->bigInteger('TransactionTypeID');
            $table->date('DocDate');
            $table->bigInteger('CurrencyID');
            $table->decimal('ExchangeRate', 18, 6)->default(1);
            $table->decimal('Amount', 18);
            $table->decimal('AmountBase', 18)->nullable();
            $table->string('Reference', 100)->nullable();
            $table->string('Narration', 300)->nullable();
            $table->string('Status', 20)->default('Draft');
            $table->boolean('IsActive')->default(true);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['BankTxnID'], 'pk__t_banktr__bc8c78e0dd4c6aa1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BankTransactions');
    }
};
