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
        Schema::table('t_LeaseCreation', function (Blueprint $table) {
            $table->foreignId('CurrencyId')->nullable()->constrained('t_Currencies','Id');
            $table->foreignId('TaxId')->nullable()->constrained('t_FinanceTaxRuleConfiguration','Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_LeaseCreation', function (Blueprint $table) {
            $table->dropForeign(['CurrencyId']);
            $table->dropForeign(['TaxId']);
            $table->dropColumn('CurrencyId');
            $table->dropColumn('TaxId');
        });
    }
};
