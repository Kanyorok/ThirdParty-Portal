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
        Schema::create('t_RentReceipt', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TenantId')->constrained('t_RentInvoice', 'Id');
            $table->foreignId('InvoiceID')->constrained('t_RentInvoice', 'Id');
            $table->string('BillingMonth');
            $table->date('InvoiceDate');
            $table->integer('RentAmount');
            $table->integer('ServicesCharge');
            $table->integer('OtherCharges');
            $table->integer('TotalDue');
            $table->integer('AmountPaid');
            $table->integer('Balance');
            $table->date('PaymentDate');
            $table->integer('Amount');
            $table->string('PaymentMethod');
            $table->string('ReferenceNo');
            $table->string('Remarks');
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
        Schema::dropIfExists('t_RentReceipt');
    }
};
