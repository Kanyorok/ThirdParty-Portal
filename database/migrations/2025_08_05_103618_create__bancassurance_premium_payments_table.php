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
        Schema::create('t_BancassurancePremiumPayments ', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('PolicyID')->Constrained('t_BancassurancePolicies', 'Id');
            $table->string('CustomerID');
            $table->string('PaymentFrequency');
            $table->date('PaymentDate');
            $table->date('NextPaymentDate');
            $table->float('Amount');
            $table->foreignId('PaymentMode')->constrained('t_CodeDetails', 'ID');
            $table->string('ReferenceNumber');
            $table->string('Notes');
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
        Schema::dropIfExists('t_BancassurancePremiumPayments');
    }
};
