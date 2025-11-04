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
        Schema::create('t_RentReceipt', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('InvoiceID');
            $table->string('BillingMonth');
            $table->date('InvoiceDate');
            $table->integer('RentAmount');
            $table->integer('ServicesCharge');
            $table->integer('ParkingFee');
            $table->integer('OtherCharges');
            $table->integer('TotalDue');
            $table->integer('AmountPaidSoFar');
            $table->integer('Balance');
            $table->date('PaymentDate');
            $table->integer('AmountPaidNow');
            $table->string('ReferenceNo');
            $table->string('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('PaymentMethod')->nullable();

            $table->primary(['Id'], 'pk__t_rentre__3214ec07e5afe594');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RentReceipt');
    }
};
