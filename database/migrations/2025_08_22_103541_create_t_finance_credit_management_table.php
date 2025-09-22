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
        Schema::create('t_FinanceCreditManagement', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('CustomerID')->constrained('t_TenantMaintenance', 'Id');
            $table->decimal('CreditLimit', 15, 2);
            $table->string('PaymentTerms')->constrained('t_CodeDetails', 'Value');
            $table->date('EffectiveFrom');
            $table->date('ExpiryDate');
            $table->string('Colleteral');
            $table->text('Remarks');
            $table->string('Status')->default('Active');
            $table->string('ApprovalStatus')->default('Pending');
            $table->text('ApprovalReason')->nullable();

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
        Schema::dropIfExists('t_FinanceCreditManagement');
    }
};
