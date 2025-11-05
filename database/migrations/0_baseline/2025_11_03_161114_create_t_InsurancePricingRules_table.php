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
        Schema::create('t_InsurancePricingRules', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('InsuranceProviderId');
            $table->bigInteger('Product');
            $table->string('RuleName');
            $table->float('CoverageAmountMin');
            $table->float('CoverageAmountMax');
            $table->float('PremiumRate');
            $table->integer('AgeMin');
            $table->integer('AgeMax');
            $table->integer('TenureMin');
            $table->integer('TenureMax');
            $table->boolean('IsActive');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_insura__3214ec07b40f46d0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_InsurancePricingRules');
    }
};
