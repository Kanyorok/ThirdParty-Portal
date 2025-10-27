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
        Schema::create('t_FinanceReceiptAllocations', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ReceiptID')->constrained('t_FinanceReceipts', 'Id')->onDelete('cascade');
            $table->foreignId('InvoiceID')->constrained('t_FinanceInvoices', 'Id');
            $table->decimal('AmountAllocated', 15, 2);
            $table->text('AllocationNotes')->nullable();

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->unique(['ReceiptID', 'InvoiceID'], 'unique_receipt_invoice_allocation');
            $table->index(['InvoiceID', 'AmountAllocated']);
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
