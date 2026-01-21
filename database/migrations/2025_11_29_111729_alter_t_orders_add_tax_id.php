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
        Schema::table('t_Orders', function (Blueprint $table) {
            $table->foreignId('TaxID')->nullable()->constrained('t_FinanceTaxRuleConfiguration', 'Id');
            //TaxPercentage
            $table->decimal('TaxPercentage', 10, 4)->default(0);    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Orders', function (Blueprint $table) {
            $table->dropForeign(['TaxID']);
            $table->dropColumn('TaxID');
            $table->dropColumn('TaxPercentage');
        });
    }
};
