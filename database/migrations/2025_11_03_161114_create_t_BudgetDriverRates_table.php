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
        Schema::create('t_BudgetDriverRates', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('PeriodTypeID');
            $table->bigInteger('RateTypeID');
            $table->decimal('RateValue', 5)->default(0);
            $table->dateTime('EffectiveDate');
            $table->string('Source');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ProductTypeId')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec076a0c7d8b');
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
