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
        Schema::create('t_FinanceCustomerWalletTransactions', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('WalletID');
            $table->bigInteger('CustomerID');
            $table->enum('TransactionType', ['deposit', 'withdrawal', 'refund', 'adjustment']);
            $table->decimal('Amount', 15);
            $table->decimal('RunningBalance', 15);
            $table->string('ReferenceType', 50);
            $table->bigInteger('ReferenceID')->nullable();
            $table->text('Description');
            $table->date('TransactionDate');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec0769584dfb');
            $table->index(['CustomerID', 'TransactionDate']);
            $table->index(['TransactionType', 'ReferenceType', 'ReferenceID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceCustomerWalletTransactions');
    }
};
