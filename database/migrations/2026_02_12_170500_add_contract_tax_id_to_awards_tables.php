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
        if (Schema::hasTable('t_TenderAwards') && !Schema::hasColumn('t_TenderAwards', 'ContractTaxID')) {
            Schema::table('t_TenderAwards', function (Blueprint $table) {
                $table->foreignId('ContractTaxID')
                    ->nullable()
                    ->after('ContractValue')
                    ->constrained('t_FinanceTaxRuleConfiguration', 'Id');
            });
        }

        if (Schema::hasTable('t_RFQAward') && !Schema::hasColumn('t_RFQAward', 'ContractTaxID')) {
            Schema::table('t_RFQAward', function (Blueprint $table) {
                $table->foreignId('ContractTaxID')
                    ->nullable()
                    ->after('ContractValue')
                    ->constrained('t_FinanceTaxRuleConfiguration', 'Id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('t_TenderAwards') && Schema::hasColumn('t_TenderAwards', 'ContractTaxID')) {
            Schema::table('t_TenderAwards', function (Blueprint $table) {
                try {
                    $table->dropForeign(['ContractTaxID']);
                } catch (\Throwable $e) {
                    // Keep rollback resilient across environments with schema drift.
                }
                $table->dropColumn('ContractTaxID');
            });
        }

        if (Schema::hasTable('t_RFQAward') && Schema::hasColumn('t_RFQAward', 'ContractTaxID')) {
            Schema::table('t_RFQAward', function (Blueprint $table) {
                try {
                    $table->dropForeign(['ContractTaxID']);
                } catch (\Throwable $e) {
                    // Keep rollback resilient across environments with schema drift.
                }
                $table->dropColumn('ContractTaxID');
            });
        }
    }
};
