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
            $table->bigIncrements('Id');
            $table->bigInteger('CreditID');
            $table->bigInteger('CustomerID');
            $table->enum('AdjustmentType', ['increase', 'decrease', 'revision']);
            $table->decimal('Amount', 15);
            $table->decimal('NewCreditLimit', 15);
            $table->text('Reason');
            $table->enum('ReferenceType', ['credit_review', 'customer_request', 'business_growth', 'risk_assessment', 'management_decision']);
            $table->bigInteger('ReferenceID')->nullable();
            $table->bigInteger('RequestedBy');
            $table->enum('ApprovalStatus', ['draft', 'approved', 'rejected'])->default('draft')->index();
            $table->text('ApprovalReason')->nullable();
            $table->bigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->date('EffectiveFrom');
            $table->text('Notes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn')->useCurrent();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec072ffda9d5');
            $table->index(['CreditID', 'ApprovalStatus']);
            $table->index(['CustomerID', 'CreatedOn']);
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
