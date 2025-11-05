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
        Schema::create('t_FinanceTaxRuleConfiguration', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TaxTypeId')->constrained('t_FinanceTaxType', 'Id');
            $table->foreignId('JurisdictionId')->constrained('t_FinanceTaxJurisdiction','Id');
            $table->decimal('Rate', 10,2);
            $table->string('AppliesTo');
            $table->decimal('ThresholdAmount', 10,2);
            $table->string('ApplyTaxPer');
            $table->date('EffectiveFrom');
            $table->date('EffectiveTo');
            $table->foreignId('TaxPayableGLID')->constrained('t_FinanceGLAccounts', 'Id');
            $table->foreignId('TaxReceivableGLID')->constrained('t_FinanceGLAccounts', 'Id');
            $table->boolean('Status')->default(true);
            
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
        Schema::dropIfExists('t_FinanceTaxRuleConfiguration');
    }
};
