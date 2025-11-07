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
            $table->bigIncrements('Id');
            $table->integer('ThirdPartyID')->nullable();
            $table->date('TransactionDate');
            $table->dateTime('PostingDate')->useCurrent();
            $table->string('ReferenceNumber', 100)->index();
            $table->string('IdempotencyKey', 100)->nullable()->index();
            $table->string('TransactionType', 50);
            $table->integer('TransactionTypeID')->nullable();
            $table->bigInteger('ModuleID')->nullable();
            $table->string('SourceTable', 100)->nullable();
            $table->bigInteger('GLAccountID')->index();
            $table->bigInteger('BranchID')->nullable();
            $table->bigInteger('DepartmentID')->nullable();
            $table->char('DRCR', 2)->index();
            $table->decimal('Amount', 18);
            $table->integer('CurrencyID')->nullable();
            $table->string('CurrencyCode', 10)->default('KES');
            $table->decimal('ExchangeRate', 18, 6)->default(1);
            $table->string('Narration')->nullable();
            $table->string('BatchNumber', 50)->nullable();
            $table->boolean('IsTaxable')->default(false);
            $table->boolean('IsReversal')->default(false);
            $table->bigInteger('ReversedTransactionID')->nullable();
            $table->boolean('IsPosted')->default(true);
            $table->string('Status', 20)->default('POSTED');
            $table->string('SystemDescription', 150)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('JournalRefNo')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec07fef544ae');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinancialTransactions');
    }
};
