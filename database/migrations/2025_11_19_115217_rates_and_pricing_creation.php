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
        Schema::create('t_PropertyRateAndPricing', function (Blueprint $table) {
        $table->id('Id');
        $table->foreignId('PropertyId')->constrained('t_PropertyRegistry', 'Id');
        $table->foreignId('BlockId')->nullable()->constrained('t_PropertyBlock', 'Id');
        $table->foreignId('FloorId')->nullable()->constrained('t_PropertyFloor', 'Id');
        $table->foreignId('UnitId')->nullable()->constrained('t_PropertyUnit', 'Id');
        $table->integer('Rent');
        $table->integer('ParkingFee')->nullable();
        $table->integer('ServiceCharge')->nullable();
        $table->integer('OtherCharges')->nullable();
        $table->integer('DepositAmount')->nullable();
        $table->foreignId('CurrencyId')->constrained('t_Currencies', 'Id');
        $table->foreignId('TaxId')->constrained('t_FinanceTaxRuleConfiguration', 'Id');
        $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
        $table->dateTime('CreatedOn');
        $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
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
        Schema::dropIfExists('t_PropertyRateAndPricing');
    }
};
