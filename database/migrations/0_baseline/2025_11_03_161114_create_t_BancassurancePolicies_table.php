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
        Schema::create('t_BancassurancePolicies', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CustomerID');
            $table->bigInteger('ProductID');
            $table->bigInteger('InsurerID');
            $table->string('PolicyNumber');
            $table->float('SumAssured');
            $table->float('PremiumAmount');
            $table->date('PolicyStartDate');
            $table->date('PolicyEndDate');
            $table->bigInteger('PaymentFrequency');
            $table->date('IssuedDate');
            $table->date('ExpiryDate');
            $table->boolean('IsActive');
            $table->string('Status');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ReferralID')->nullable();
            $table->bigInteger('RiderAddOnId')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec07cf4c05bf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassurancePolicies');
    }
};
