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
        Schema::create('t_FinanceCreditManagement', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CustomerID')->nullable();
            $table->decimal('CreditLimit', 15);
            $table->string('PaymentTerms');
            $table->date('EffectiveFrom');
            $table->date('ExpiryDate');
            $table->string('Colleteral');
            $table->text('Remarks');
            $table->string('Status')->default('Active');
            $table->string('ApprovalStatus')->default('Pending');
            $table->text('ApprovalReason')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->integer('RiskScore')->default(50);
            $table->enum('RiskLevel', ['Low', 'Medium', 'High'])->default('Medium');
            $table->integer('ReviewCycleMonths')->default(6);
            $table->date('LastReviewDate')->nullable();
            $table->date('NextReviewDate')->nullable();
            $table->text('RiskNotes')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec07dad2c254');
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
