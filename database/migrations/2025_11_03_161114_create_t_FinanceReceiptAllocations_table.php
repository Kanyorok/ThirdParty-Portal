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
        Schema::create('t_FinanceReceiptAllocations', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ReceiptID');
            $table->bigInteger('InvoiceID');
            $table->decimal('AmountAllocated', 15);
            $table->text('AllocationNotes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec078f98a0b4');
            $table->index(['InvoiceID', 'AmountAllocated']);
            $table->unique(['ReceiptID', 'InvoiceID'], 'unique_receipt_invoice_allocation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceReceiptAllocations');
    }
};
