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
            if (!Schema::hasColumn('t_RFQAward', 'AwardStatus')) {
                $table->string('AwardStatus', 50)->nullable()->after('DeletedBy')->comment('Status of the award: Pending, Approved, Rejected, Cancelled');
            }
            if (!Schema::hasColumn('t_RFQAward', 'AwardDate')) {
                $table->date('AwardDate')->nullable()->after('AwardStatus')->comment('Date when the award was made');
            }
            if (!Schema::hasColumn('t_RFQAward', 'AwardedAmount')) {
                $table->decimal('AwardedAmount', 15, 2)->nullable()->after('AwardDate')->comment('Amount awarded to the supplier');
            }

            // Contract Management Fields
            if (!Schema::hasColumn('t_RFQAward', 'ContractStatus')) {
                $table->string('ContractStatus', 50)->nullable()->after('AwardedAmount')->comment('Status of contract: Draft, Pending Approval, Approved, Sent to Legal, Active, Terminated');
            }
            if (!Schema::hasColumn('t_RFQAward', 'ContractRef')) {
                $table->string('ContractRef', 100)->nullable()->after('ContractStatus')->comment('Contract reference number');
            }
            if (!Schema::hasColumn('t_RFQAward', 'ContractValue')) {
                $table->decimal('ContractValue', 15, 2)->nullable()->after('ContractRef')->comment('Final contract value');
            }
            if (!Schema::hasColumn('t_RFQAward', 'ContractRequestRef')) {
                $table->string('ContractRequestRef', 100)->nullable()->after('ContractValue')->comment('Reference for legal module request');
            }
            if (!Schema::hasColumn('t_RFQAward', 'PaymentTerms')) {
                $table->text('PaymentTerms')->nullable()->after('ContractRequestRef')->comment('Payment terms and conditions');
            }
            if (!Schema::hasColumn('t_RFQAward', 'DeliveryTerms')) {
                $table->text('DeliveryTerms')->nullable()->after('PaymentTerms')->comment('Delivery terms and conditions');
            }
            if (!Schema::hasColumn('t_RFQAward', 'SpecialConditions')) {
                $table->text('SpecialConditions')->nullable()->after('DeliveryTerms')->comment('Special contract conditions');
            }
            if (!Schema::hasColumn('t_RFQAward', 'ContractApprovalRemarks')) {
                $table->text('ContractApprovalRemarks')->nullable()->after('SpecialConditions')->comment('Contract approval remarks');
            }
            if (!Schema::hasColumn('t_RFQAward', 'ContractApprovedBy')) {
                $table->unsignedBigInteger('ContractApprovedBy')->nullable()->after('ContractApprovalRemarks')->comment('User who approved the contract');
            }
            if (!Schema::hasColumn('t_RFQAward', 'ContractApprovedOn')) {
                $table->dateTime('ContractApprovedOn')->nullable()->after('ContractApprovedBy')->comment('Contract approval timestamp');
            }
            if (!Schema::hasColumn('t_RFQAward', 'ContractStartDate')) {
                $table->date('ContractStartDate')->nullable()->after('ContractApprovedOn')->comment('Contract start date');
            }
            if (!Schema::hasColumn('t_RFQAward', 'ContractEndDate')) {
                $table->date('ContractEndDate')->nullable()->after('ContractStartDate')->comment('Contract end date');
            }

            // Contract Lifecycle Fields
            if (!Schema::hasColumn('t_RFQAward', 'TerminationReason')) {
                $table->text('TerminationReason')->nullable()->after('ContractEndDate')->comment('Reason for contract termination');
            }
            if (!Schema::hasColumn('t_RFQAward', 'TerminationDate')) {
                $table->date('TerminationDate')->nullable()->after('TerminationReason')->comment('Date contract was terminated');
            }
            if (!Schema::hasColumn('t_RFQAward', 'SettlementDetails')) {
                $table->text('SettlementDetails')->nullable()->after('TerminationDate')->comment('Settlement details after termination');
            }
        });

        // Add indexes separately to handle cases where they might already exist
        try {
            Schema::table('t_RFQAward', function (Blueprint $table) {
                $table->index('ContractStatus', 'idx_rfqaward_contractstatus');
            });
        } catch (\Exception $e) {
            // Index may already exist
        }
        
        try {
            Schema::table('t_RFQAward', function (Blueprint $table) {
                $table->index('ContractRef', 'idx_rfqaward_contractref');
            });
        } catch (\Exception $e) {
            // Index may already exist
        }
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
