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
        Schema::create('t_BancassuranceClaimPayments', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ClaimId')->constrained('t_BancassuranceClaims','Id');
            $table->date('PaymentDate');
            $table->float('PaymentAmount');
            $table->string('PaymentReference');
            $table->string('Note');
            $table->string('PaidBy');
            $table->foreignId('PaymentMethod')->constrained('t_CodeDetails','ID');
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
        Schema::dropIfExists('t_BancassuranceClaimPayments');
    }
};
