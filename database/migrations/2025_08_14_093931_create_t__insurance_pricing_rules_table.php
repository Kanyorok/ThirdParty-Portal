<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_InsurancePricingRules', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('InsuranceProviderId')->constrained('t_InsuranceProviders', 'Id');
            $table->foreignId('Product')->constrained('t_InsuranceProducts', 'Id');
            $table->string('RuleName');
            $table->float('CoverageAmountMin');
            $table->float('CoverageAmountMax');
            $table->float('PremiumRate');
            $table->integer('AgeMin');
            $table->integer('AgeMax');
            $table->integer('TenureMin');
            $table->integer('TenureMax');
            $table->boolean('IsActive');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('DeletedOn')->nullable();
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
