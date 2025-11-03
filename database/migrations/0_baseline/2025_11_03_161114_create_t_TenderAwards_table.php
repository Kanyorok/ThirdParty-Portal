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
        Schema::create('t_TenderAwards', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('TenderID');
            $table->bigInteger('WinningSupplierID');
            $table->decimal('AwardedAmount', 18)->nullable();
            $table->text('AwardJustification');
            $table->date('AwardDate')->index();
            $table->date('ContractStartDate')->nullable();
            $table->date('ContractEndDate')->nullable();
            $table->decimal('TechnicalScore', 5)->nullable();
            $table->decimal('FinancialScore', 5)->nullable();
            $table->decimal('TotalScore', 5)->nullable();
            $table->boolean('NotifyUnsuccessfulBidders')->default(true);
            $table->enum('AwardStatus', ['Pending', 'Approved', 'Rejected', 'Cancelled'])->default('Pending')->index();
            $table->bigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->text('ApprovalRemarks')->nullable();
            $table->bigInteger('RejectedBy')->nullable();
            $table->dateTime('RejectedOn')->nullable();
            $table->text('RejectionReason')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('ContractStatus', 50)->nullable()->index();
            $table->string('ContractRef', 100)->nullable()->index();
            $table->decimal('ContractValue', 15)->nullable();
            $table->string('ContractRequestRef', 100)->nullable();
            $table->text('PaymentTerms')->nullable();
            $table->text('DeliveryTerms')->nullable();
            $table->text('SpecialConditions')->nullable();
            $table->text('ContractApprovalRemarks')->nullable();
            $table->bigInteger('ContractApprovedBy')->nullable();
            $table->dateTime('ContractApprovedOn')->nullable();
            $table->text('TerminationReason')->nullable();
            $table->date('TerminationDate')->nullable();
            $table->text('SettlementDetails')->nullable();

            $table->primary(['Id'], 'pk__t_tender__3214ec07c511bdf8');
            $table->index(['ContractStatus', 'ContractApprovedBy']);
            $table->index(['TenderID', 'AwardStatus']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenderAwards');
    }
};
