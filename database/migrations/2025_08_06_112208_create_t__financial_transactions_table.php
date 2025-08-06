<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_FinancialTransactions', function (Blueprint $table) {
            $table->id('Id');

            // Posting details
            $table->date('TransactionDate');
            $table->date('ValueDate');// Actual day the transaction was done. It can be backdated and incases where there is no then it will be equal to Transactiondate column
            $table->string('ReferenceNumber', 100);
            $table->string('SourceType', 50);//E.g., 'JOURNAL', 'VOUCHER', 'CHEQUE', 'RECEIPT', 'INVOICE', 'GRN', 'PAYMENT'
            $table->unsignedBigInteger('SourceID')->nullable(); //FK to source table (optional)
            $table->string('SourceTable', 100)->nullable(); //

            // Account posting
            $table->unsignedInteger('GLAccountID');
            $table->foreign('GLAccountID')->references('Id')->on('t_FinanceGLAccounts');
            $table->string('BranchID', 20);
            $table->string('DepartmentID', 20);

            // Financials
            $table->char('DRCR', 2);
            $table->decimal('Amount', 18, 2);
            $table->string('CurrencyCode', 10)->default('KES');
            $table->decimal('ExchangeRate', 18, 6)->default(1.000000);

            // Descriptions
            $table->string('Narration', 200)->nullable();
            $table->string('BatchNumber', 50)->nullable();

            // Reversal
            $table->boolean('IsReversal')->default(false);
            $table->unsignedBigInteger('ReversedTransactionID')->nullable();
            $table->foreign('ReversedTransactionID')->references('Id')->on('t_FinancialTransactions');

            // Control Flags
            $table->boolean('IsPosted')->default(1);
            $table->string('Status', 20)->default('POSTED');//'POSTED'

            // Audit
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            // Optional: indexing
            $table->index('ReferenceNumber');
            $table->index('SourceType');
            $table->index('GLAccountID');
            $table->index('DRCR');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_FinancialTransactions');
    }
};
