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
        Schema::table('t_FinanceInvoiceEntry', function (Blueprint $table) {
            //Add foreign ID to the TaxID column
            $table->foreignId('TaxID')->nullable()->constrained('t_FinanceTaxRuleConfiguration', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceInvoiceEntry', function (Blueprint $table) {
            //Drop the foreign key
            $table->dropForeign(['TaxID']);
            //Drop the column
            $table->dropColumn('TaxID');
        });
    }
};
