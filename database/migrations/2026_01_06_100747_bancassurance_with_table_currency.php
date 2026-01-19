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
        Schema::table('t_BancassuranceCommissionPayouts', function (Blueprint $table) {
            $table->foreignId('CurrencyId')->nullable()->constraint('t_Currencies', 'Id');
            $table->foreignId('CommissionRuleId')->nullable()->constraint('t_BancassuranceCommissionRules', 'Id');
            $table->renameColumn('PaidBy', 'PaidTo');
        });
        Schema::table('t_BancassuranceClaims', function (Blueprint $table) {
            $table->foreignId('CurrencyId')->nullable()->constraint('t_Currencies', 'Id');
        });
        Schema::table('t_BancassuranceClaimPayments', function (Blueprint $table) {
            $table->renameColumn('PaidBy', 'PaidTo');
        });

        Schema::table('t_BancassurancePremiumPayments', function (Blueprint $table) {
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
        Schema::table('t_BancassuranceCommissionPayouts', function (Blueprint $table) {
            // $table->dropForeign(['CurrencyId']);
            // $table->dropForeign(['CommissionRuleId']);
            $table->dropColumn('CurrencyId');
            $table->dropColumn('CommissionRuleId');
            $table->renameColumn('PaidTo', 'PaidBy');
        });

        Schema::table('t_BancassuranceClaims', function (Blueprint $table) {
            // $table->dropForeign(['CurrencyId']);
            $table->dropColumn('CurrencyId');
        });
        Schema::table('t_BancassuranceClaimPayments', function (Blueprint $table) {
            // $table->dropForeign(['CurrencyId']);
            $table->renameColumn('PaidTo', 'PaidBy');
        });
        Schema::table('t_BancassurancePremiumPayments', function (Blueprint $table) {
            // $table->dropForeign(['CurrencyId']);
            $table->dropColumn('CurrencyId');
        });

    }
};
