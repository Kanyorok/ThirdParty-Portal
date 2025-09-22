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
        Schema::create('t_FinanceInvoiceLines', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('InvoiceID')->constrained('t_FinanceInvoices', 'Id');
            $table->string('InvoiceLineName', 150);
            $table->string('Description', 250)->nullable();
            $table->integer('UnitCost')->default(0);
            $table->integer('Quantity')->default(1);

            $table->string('Tax', 10)->nullable();
            $table->string('TaxID', 10)->nullable();
            $table->string('TaxAmount', 10)->default(0);

            $table->integer('CurrencyID')->nullable();

            $table->integer('Discount')->nullable();
            $table->integer('Total');


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
        Schema::dropIfExists('t_FinanceInvoiceLines');
    }
};
