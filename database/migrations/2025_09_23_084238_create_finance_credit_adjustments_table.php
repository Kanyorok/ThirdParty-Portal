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
        Schema::create('t_FinanceCreditAdjustments', function (Blueprint $table) {
            $table->id('Id');
            
            // Foreign keys
            $table->unsignedBigInteger('CreditID');
            $table->unsignedBigInteger('CustomerID');
            
            // Adjustment details
            $table->enum('AdjustmentType', ['increase', 'decrease', 'revision']);
            $table->decimal('Amount', 15, 2);
            $table->decimal('NewCreditLimit', 15, 2);
            $table->text('Reason');
            $table->enum('ReferenceType', ['credit_review', 'customer_request', 'business_growth', 'risk_assessment', 'management_decision']);
            $table->unsignedBigInteger('ReferenceID')->nullable();
            $table->unsignedBigInteger('RequestedBy');
            
            // Approval workflow
            $table->enum('ApprovalStatus', ['draft', 'approved', 'rejected'])->default('draft');
            $table->text('ApprovalReason')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->timestamp('ApprovedOn')->nullable();
            
            // Effective date
            $table->date('EffectiveFrom');
            $table->text('Notes')->nullable();
            
            // Audit fields
            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy');
            $table->timestamp('ModifiedOn')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->timestamp('DeletedOn')->nullable();
            
            // Indexes
            $table->index(['CreditID', 'ApprovalStatus']);
            $table->index(['CustomerID', 'CreatedOn']);
            $table->index('ApprovalStatus');
            
            // Foreign key constraints
            $table->foreign('CreditID')->references('Id')->on('t_FinanceCreditManagement');
            $table->foreign('CustomerID')->references('Id')->on('t_ThirdParties');
            $table->foreign('RequestedBy')->references('Id')->on('t_Users');
            $table->foreign('ApprovedBy')->references('Id')->on('t_Users');
            $table->foreign('CreatedBy')->references('Id')->on('t_Users');
            $table->foreign('ModifiedBy')->references('Id')->on('t_Users');
            $table->foreign('DeletedBy')->references('Id')->on('t_Users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceCreditAdjustments');
    }
};
