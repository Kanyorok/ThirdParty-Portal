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
        Schema::create('t_LeaseCreation', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('LeaseNumber')->unique();
            $table->bigInteger('Tenant');
            $table->bigInteger('PropertyID');
            $table->bigInteger('BlockID');
            $table->bigInteger('FloorID');
            $table->bigInteger('Unit');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->bigInteger('PaymentFrequency');
            $table->float('MonthlyRent');
            $table->float('Deposit');
            $table->integer('DueDay');
            $table->string('SpecialTerms');
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->float('ServiceCharge')->nullable();
            $table->float('ParkingFee')->nullable();
            $table->float('OtherCharges')->nullable();
            $table->string('Status', 1)->nullable()->default('n');

            $table->primary(['Id'], 'pk__t_leasec__3214ec07cdb7a132');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LeaseCreation');
    }
};
