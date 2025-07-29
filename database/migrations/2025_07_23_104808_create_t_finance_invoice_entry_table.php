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
        Schema::create('t_FinanceInvoiceEntry', function (Blueprint $table) {
            $table->id('Id');
            $table->string('InvoiceNumber');
            $table->foreignId('SupplierID')->constrained('t_Suppliers', 'Id');
            $table->foreignId('CurrencyID')->constrained('t_Currencies', 'Id');
            $table->decimal('ExchangeRate', 10, 4)->default(1.0000);
            $table->foreignId('POReference')->constrained('t_Orders', 'Id');
            $table->foreignId('GRNReference')->constrained('t_GoodsReceipts', 'Id');
            $table->date('InvoiceDate');
            $table->decimal('InvoiceAmount', 10, 2);
            $table->text('Description');
            
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceInvoiceEntry');
    }
};
