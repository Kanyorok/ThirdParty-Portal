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
        Schema::table('t_InsuranceProductRiders', function (Blueprint $table) {
            $table->foreignId('CurrencyId')->nullable()->constraint('t_Currencies', 'Id');
        });
        Schema::table('t_InsurancePricingRules', function (Blueprint $table) {
            $table->foreignId('CurrencyId')->nullable()->constraint('t_Currencies', 'Id');
        });
        Schema::table('t_BancassuranceCommissionRules', function (Blueprint $table) {
            $table->foreignId('CurrencyId')->nullable()->constraint('t_Currencies', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_InsuranceProductRiders', function (Blueprint $table) {
            // $table->dropForeign(['CurrencyId']);
            $table->dropColumn('CurrencyId');
        });
        Schema::table('t_InsurancePricingRules', function (Blueprint $table) {
            // $table->dropForeign(['CurrencyId']);
            $table->dropColumn('CurrencyId');
        });
        Schema::table('t_BancassuranceCommissionRules', function (Blueprint $table) {
            // $table->dropForeign(['CurrencyId']);
            $table->dropColumn('CurrencyId');
        });

    }
};
