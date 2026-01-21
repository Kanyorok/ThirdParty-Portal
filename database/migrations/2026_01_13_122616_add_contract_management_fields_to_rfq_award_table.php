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
        Schema::table('t_RFQAward', function (Blueprint $table) {
            // Award Fields
            $table->string('AwardStatus', 50)->nullable()->after('DeletedBy')->comment('Status of the award: Pending, Approved, Rejected, Cancelled');
            $table->date('AwardDate')->nullable()->after('AwardStatus')->comment('Date when the award was made');
            $table->decimal('AwardedAmount', 15, 2)->nullable()->after('AwardDate')->comment('Amount awarded to the supplier');

            // Contract Management Fields
            $table->string('ContractStatus', 50)->nullable()->after('AwardedAmount')->comment('Status of contract: Draft, Pending Approval, Approved, Sent to Legal, Active, Terminated');
            $table->string('ContractRef', 100)->nullable()->after('ContractStatus')->comment('Contract reference number');
            $table->decimal('ContractValue', 15, 2)->nullable()->after('ContractRef')->comment('Final contract value');
            $table->string('ContractRequestRef', 100)->nullable()->after('ContractValue')->comment('Reference for legal module request');
            $table->text('PaymentTerms')->nullable()->after('ContractRequestRef')->comment('Payment terms and conditions');
            $table->text('DeliveryTerms')->nullable()->after('PaymentTerms')->comment('Delivery terms and conditions');
            $table->text('SpecialConditions')->nullable()->after('DeliveryTerms')->comment('Special contract conditions');
            $table->text('ContractApprovalRemarks')->nullable()->after('SpecialConditions')->comment('Contract approval remarks');
            $table->foreignId('ContractApprovedBy')->nullable()->constrained('t_Users', 'Id')->after('ContractApprovalRemarks')->comment('User who approved the contract');
            $table->dateTime('ContractApprovedOn')->nullable()->after('ContractApprovedBy')->comment('Contract approval timestamp');
            $table->date('ContractStartDate')->nullable()->after('ContractApprovedOn')->comment('Contract start date');
            $table->date('ContractEndDate')->nullable()->after('ContractStartDate')->comment('Contract end date');

            // Contract Lifecycle Fields
            $table->text('TerminationReason')->nullable()->after('ContractEndDate')->comment('Reason for contract termination');
            $table->date('TerminationDate')->nullable()->after('TerminationReason')->comment('Date contract was terminated');
            $table->text('SettlementDetails')->nullable()->after('TerminationDate')->comment('Settlement details after termination');

            // Add indexes for commonly queried fields
            $table->index('ContractStatus');
            $table->index('ContractRef');
            $table->index(['ContractStatus', 'ContractApprovedBy']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQAward', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['ContractStatus', 'ContractApprovedBy']);
            $table->dropIndex(['ContractRef']);
            $table->dropIndex(['ContractStatus']);

            // Drop foreign key constraint
            $table->dropForeign(['ContractApprovedBy']);

            // Drop all contract management columns
            $table->dropColumn([
                'AwardStatus',
                'AwardDate',
                'AwardedAmount',
                'ContractStatus',
                'ContractRef',
                'ContractValue',
                'ContractRequestRef',
                'PaymentTerms',
                'DeliveryTerms',
                'SpecialConditions',
                'ContractApprovalRemarks',
                'ContractApprovedBy',
                'ContractApprovedOn',
                'ContractStartDate',
                'ContractEndDate',
                'TerminationReason',
                'TerminationDate',
                'SettlementDetails'
            ]);
        });
    }
};
