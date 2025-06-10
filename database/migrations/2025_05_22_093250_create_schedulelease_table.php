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
        Schema::create('t_ScheduleLease', function (Blueprint $table) {
            $table->id();
            $table->string('leaseID');
            $table->string('PaymentFrequency');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->integer('BaseRent');
            $table->integer('ServiceCharge');
            $table->integer('ParkingFee');
            $table->integer('OtherCharges');
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
        Schema::dropIfExists('t_ScheduleLease');
    }
};
