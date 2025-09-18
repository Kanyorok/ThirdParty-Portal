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
            $table->id('Id');
            $table->foreignId('TenderID')->constrained('t_Tenders', 'Id');
            $table->foreignId('WinningSupplierID')->constrained('t_Suppliers', 'Id');
            $table->decimal('AwardedAmount', 18, 2)->nullable();
            $table->text('AwardJustification');
            $table->date('AwardDate');
            $table->date('ContractStartDate')->nullable();
            $table->date('ContractEndDate')->nullable();
            $table->decimal('TechnicalScore', 5, 2)->nullable();
            $table->decimal('FinancialScore', 5, 2)->nullable();
            $table->decimal('TotalScore', 5, 2)->nullable();
            $table->boolean('NotifyUnsuccessfulBidders')->default(true);
            $table->enum('AwardStatus', ['Pending', 'Approved', 'Rejected', 'Cancelled'])->default('Pending');
            $table->foreignId('ApprovedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ApprovedOn')->nullable();
            $table->text('ApprovalRemarks')->nullable();
            $table->foreignId('RejectedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('RejectedOn')->nullable();
            $table->text('RejectionReason')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
            
            // Indexes for better performance
            $table->index(['TenderID', 'AwardStatus']);
            $table->index('AwardDate');
            $table->index('AwardStatus');
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
