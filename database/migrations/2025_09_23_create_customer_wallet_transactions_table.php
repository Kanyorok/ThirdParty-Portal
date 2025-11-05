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
        Schema::create('t_FinanceCustomerWalletTransactions', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('WalletID')->constrained('t_FinanceCustomerWallet', 'Id');
            $table->foreignId('CustomerID')->constrained('t_ThirdParties', 'Id');
            $table->enum('TransactionType', ['deposit', 'withdrawal', 'refund', 'adjustment']);
            $table->decimal('Amount', 15, 2);
            $table->decimal('RunningBalance', 15, 2);
            $table->string('ReferenceType', 50); // 'receipt', 'invoice', 'manual', etc.
            $table->unsignedBigInteger('ReferenceID')->nullable();
            $table->text('Description');
            $table->date('TransactionDate');

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

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
