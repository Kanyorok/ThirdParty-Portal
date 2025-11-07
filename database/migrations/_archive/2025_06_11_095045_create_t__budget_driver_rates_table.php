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
        Schema::create('t_BudgetDriverRates', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('PeriodTypeID')->constrained('t_BudgetPeriodTypes', 'Id');
            $table->foreignId('ProductTypeID')->constrained('t_BudgetProductTypes', 'Id');// Change to point to t_BudgetProducts
            $table->foreignId('RateTypeID')->constrained('t_BudgetRates', 'Id');
            $table->decimal('RateValue', 5, 2)->default(0.00)->unsigned()->check('RateValue <= 100.00');
            $table->dateTime('EffectiveDate');
            $table->string('Source');

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
        Schema::dropIfExists('t_BudgetDriverRates');
    }
};
