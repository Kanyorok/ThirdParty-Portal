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
        Schema::create('t_FinanceInvoiceTaxes', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('APInvoiceID')->nullable()->constrained('t_FinanceInvoices', 'Id');
            $table->foreignId('ARInvoiceID')->nullable()->constrained('t_FinanceInvoices', 'Id');
            $table->foreignId('TaxID')->constrained('t_FinanceTaxRuleConfiguration', 'Id');
            $table->decimal('TaxAmount', 10, 2)->default(0);
            $table->decimal('TaxPercentage', 10, 2)->default(0);
            $table->decimal('AmountPaid', 10, 2)->default(0);
            $table->string('SourceType')->nullable();
            $table->string('Sourcetable')->nullable();
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
        Schema::dropIfExists('t_FinanceInvoiceTaxes');
    }
};
