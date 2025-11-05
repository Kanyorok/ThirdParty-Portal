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
        Schema::create('t_RenewLease', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('LeaseNumber');
            $table->bigInteger('PaymentFrequency');
            $table->date('EndDateCurrentLease');
            $table->date('NewStartDate');
            $table->date('NewEndDate');
            $table->integer('NewMonthlyRent');
            $table->string('Remarks');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->float('ServiceCharge')->nullable();
            $table->float('ParkingFee')->nullable();
            $table->float('OtherCharges')->nullable();
            $table->boolean('IsActive')->nullable()->default(true);

            $table->primary(['Id'], 'pk__t_renewl__3214ec075611e56e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RenewLease');
    }
};
