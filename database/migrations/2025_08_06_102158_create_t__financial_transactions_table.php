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
        Schema::create('t_FinancialTransactions', function (Blueprint $table) {
            $table->id('Id');

            // Posting details
            $table->date('TransactionDate');//The actual day the transaction happened.
            $table->dateTime('PostingDate')->default(DB::raw('GETDATE()'));// The day the transaction was posted
            $table->string('ReferenceNumber', 100);//Source reference (e.g., Voucher No, Journal No, Cheque No)
            $table->string('TransactionType', 50);// E.g Journal, Invoice Payable. Originating from mapping
            $table->unsignedBigInteger('ModuleID')->nullable();
            $table->string('SourceTable', 100)->nullable();

            // Account posting
            $table->foreignId('GLAccountID')->constrained('t_FinanceGLAccounts','Id');
            $table->foreignId('BranchID')->nullable()->constrained('t_Branches', 'Id');
            $table->foreignId('DepartmentID')->nullable()->constrained('t_Departments', 'Id');


            // Financials
            $table->char('DRCR', 2);
            $table->decimal('Amount', 18, 2);
            $table->integer('CurrencyID')->nullable();
            $table->string('CurrencyCode', 10)->default('KES');
            $table->decimal('ExchangeRate', 18, 6)->default(1.000000);

            // Descriptions
            $table->string('Narration', 255)->nullable();
            $table->string('BatchNumber', 50)->nullable();

            //Taxable flags
            $table->boolean('IsTaxable')->default(false);// if its a posting for tax

            // Reversal
            $table->boolean('IsReversal')->default(0);
            $table->unsignedBigInteger('ReversedTransactionID')->nullable();
            $table->foreign('ReversedTransactionID')->references('Id')->on('t_FinancialTransactions');

            // Control Flags
            $table->boolean('IsPosted')->default(1);
            $table->string('Status', 20)->default('POSTED');

            //System Description
            $table->string('SystemDescription', 150)->nullable();

            // Audit
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            // indexing
            $table->index('ReferenceNumber');
            $table->index('GLAccountID');
            $table->index('DRCR');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_FinancialTransactions');
    }
};
