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
        Schema::table('t_Orders', function (Blueprint $table) {
            // LPO Origination Type and References
            $table->string('OriginationType', 50)->nullable()->after('ExtOrdNum')
                ->comment('Type of LPO origination: rfq, contract, award, direct_procurement');

            $table->string('OriginationRef', 100)->nullable()->after('OriginationType')
                ->comment('General reference to originating record');

            // Specific References for each origination type
            $table->foreignId('ContractRef')->nullable()->after('OriginationRef')
                ->constrained('t_TenderAwards', 'Id')->comment('Reference to contract for contract-based LPOs');

            $table->foreignId('AwardRef')->nullable()->after('ContractRef')
                ->constrained('t_TenderAwards', 'Id')->comment('Reference to award for award-based LPOs');

            $table->foreignId('PlanRef')->nullable()->after('AwardRef')
                ->constrained('t_ConsolidatedProcurementPlan', 'PlanID')->comment('Reference to procurement plan for direct procurement LPOs');

            // Additional LPO Management Fields
            $table->decimal('TotalAmount', 15, 2)->nullable()->after('PlanRef')
                ->comment('Total LPO amount');

            $table->text('Notes')->nullable()->after('TotalAmount')
                ->comment('Additional notes and instructions');

            $table->text('DeliveryTerms')->nullable()->after('Notes')
                ->comment('Delivery terms and conditions');

            $table->string('Status', 50)->nullable()->after('DeliveryTerms')
                ->comment('LPO Status: draft, pending_approval, approved, issued, delivered, completed, cancelled');

            // Indexes for performance
            $table->index('OriginationType');
            $table->index(['OriginationType', 'Status']);
            $table->index(['ContractRef', 'Status']);
            $table->index(['AwardRef', 'Status']);
            $table->index(['PlanRef', 'Status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Orders', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['t_Orders_OriginationType_index']);
            $table->dropIndex(['t_Orders_OriginationType_Status_index']);
            $table->dropIndex(['t_Orders_ContractRef_Status_index']);
            $table->dropIndex(['t_Orders_AwardRef_Status_index']);
            $table->dropIndex(['t_Orders_PlanRef_Status_index']);

            // Drop foreign key constraints
            $table->dropForeign(['ContractRef']);
            $table->dropForeign(['AwardRef']);
            $table->dropForeign(['PlanRef']);

            // Drop columns
            $table->dropColumn([
                'OriginationType',
                'OriginationRef',
                'ContractRef',
                'AwardRef',
                'PlanRef',
                'TotalAmount',
                'Notes',
                'DeliveryTerms',
                'Status'
            ]);
        });
    }
};
