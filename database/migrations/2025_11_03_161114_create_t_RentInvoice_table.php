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
        Schema::create('t_RentInvoice', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('InvoiceNumber')->unique();
            $table->bigInteger('Lease');
            $table->string('BillingMonth');
            $table->string('InvoiceNotes');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->date('InvoiceDate')->nullable();
            $table->decimal('RentAmount', 20)->nullable();
            $table->decimal('ServicesCharge', 20)->nullable();
            $table->decimal('OtherCharges', 20)->nullable();
            $table->decimal('ParkingFee', 20)->nullable();
            $table->string('Status', 1)->nullable();
            $table->string('RequestID', 60)->nullable();

            $table->primary(['Id'], 'pk__t_rentin__3214ec07a0700eb0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RentInvoice');
    }
};
