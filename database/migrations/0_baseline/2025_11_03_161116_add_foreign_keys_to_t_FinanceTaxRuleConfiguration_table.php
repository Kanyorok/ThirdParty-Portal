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
        Schema::table('t_FinanceTaxRuleConfiguration', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['JurisdictionId'])->references(['Id'])->on('t_FinanceTaxJurisdiction')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TaxPayableGLID'])->references(['Id'])->on('t_FinanceGLAccounts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TaxReceivableGLID'])->references(['Id'])->on('t_FinanceGLAccounts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TaxTypeId'])->references(['Id'])->on('t_FinanceTaxType')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceTaxRuleConfiguration', function (Blueprint $table) {
            $table->dropForeign('t_financetaxruleconfiguration_createdby_foreign');
            $table->dropForeign('t_financetaxruleconfiguration_deletedby_foreign');
            $table->dropForeign('t_financetaxruleconfiguration_jurisdictionid_foreign');
            $table->dropForeign('t_financetaxruleconfiguration_modifiedby_foreign');
            $table->dropForeign('t_financetaxruleconfiguration_taxpayableglid_foreign');
            $table->dropForeign('t_financetaxruleconfiguration_taxreceivableglid_foreign');
            $table->dropForeign('t_financetaxruleconfiguration_taxtypeid_foreign');
        });
    }
};
