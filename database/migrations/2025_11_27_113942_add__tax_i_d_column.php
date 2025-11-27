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
        Schema::table('t_FinanceInvoices', function (Blueprint $table) {
            //Add foreign ID to the TaxID column
            $table->foreignId('TaxID')->nullable()->constrained('t_FinanceTaxRuleConfiguration', 'Id');
            $table->decimal('TaxAmount', 10, 2)->default(0);
            //Tax Percentage
            $table->decimal('TaxPercentage', 10, 4)->default(0);
            $table->decimal('InvoiceAmount', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceInvoices', function (Blueprint $table) {
            //Drop the foreign key
            $table->dropForeign(['TaxID']);
            //Drop the column
            $table->dropColumn('TaxID');
            $table->dropColumn('TaxAmount');
            $table->dropColumn('TaxPercentage');
            $table->dropColumn('InvoiceAmount');
        });
    }
};
