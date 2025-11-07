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
        Schema::create('t_BancassurancePremiumPayments ', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('PolicyID');
            $table->string('CustomerID');
            $table->string('PaymentFrequency');
            $table->date('PaymentDate');
            $table->date('NextPaymentDate');
            $table->float('Amount');
            $table->bigInteger('PaymentMode');
            $table->string('ReferenceNumber');
            $table->string('Notes');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec070a3af46e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassurancePremiumPayments ');
    }
};
