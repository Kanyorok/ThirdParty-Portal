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
        Schema::create('t_RentInvoice', function (Blueprint $table) {
            $table->id('Id');
            $table->string('InvoiceNumber')->unique();
            $table->foreignId('TenantId')->constrained('t_LeaseCreation', 'Id');
            $table->foreignId('Lease')->constrained('t_LeaseCreation', 'Id');
            $table->string('BillingMonth');
            $table->string('InvoiceDate');
            $table->string('RentAmount');
            $table->string('ServicesCharge');
            $table->string('OtherCharges');
            $table->string('InvoiceNotes');
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
        Schema::dropIfExists('t_RentInvoice');
    }
};
