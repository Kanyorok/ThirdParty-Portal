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
            $table->bigIncrements('Id');
            $table->string('InvoiceNumber');
            $table->bigInteger('SupplierID');
            $table->bigInteger('CurrencyID');
            $table->decimal('ExchangeRate', 10, 4)->default(1);
            $table->bigInteger('POReference');
            $table->bigInteger('GRNReference');
            $table->date('InvoiceDate');
            $table->decimal('InvoiceAmount', 10);
            $table->enum('ApprovalStatus', ['draft', 'posted', 'rejected'])->default('draft');
            $table->text('ApprovalReason')->nullable();
            $table->string('Status', 20)->default('draft');
            $table->string('DocumentTypeID')->nullable();
            $table->text('Description');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->date('DueDate')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec074c9bbaa1');
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
