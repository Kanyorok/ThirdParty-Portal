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
        Schema::create('t_FinanceInvoiceLines', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('InvoiceID');
            $table->string('InvoiceLineName', 150);
            $table->string('Description', 250)->nullable();
            $table->integer('UnitCost')->default(0);
            $table->integer('Quantity')->default(1);
            $table->string('Tax', 10)->nullable();
            $table->string('TaxID', 10)->nullable();
            $table->string('TaxAmount', 10)->default('0');
            $table->integer('CurrencyID')->nullable();
            $table->integer('Discount')->nullable();
            $table->integer('Total');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec0722d733f4');
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
