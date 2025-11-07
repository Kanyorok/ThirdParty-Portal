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
        Schema::create('t_ScheduleLease', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('LeaseNumber');
            $table->bigInteger('PaymentFrequency');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->float('BaseRent');
            $table->float('ServiceCharge');
            $table->float('ParkingFee');
            $table->float('OtherCharges');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->boolean('IsActive')->nullable()->default(true);

            $table->primary(['Id'], 'pk__t_schedu__3214ec07ddd13546');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ScheduleLease');
    }
};
