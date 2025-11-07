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
            $table->bigIncrements('Id');
            $table->bigInteger('TaxTypeId');
            $table->bigInteger('JurisdictionId');
            $table->decimal('Rate', 10);
            $table->string('AppliesTo');
            $table->decimal('ThresholdAmount', 10);
            $table->string('ApplyTaxPer');
            $table->date('EffectiveFrom');
            $table->date('EffectiveTo');
            $table->bigInteger('TaxPayableGLID');
            $table->bigInteger('TaxReceivableGLID');
            $table->boolean('Status')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec07e0cb58fc');
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
