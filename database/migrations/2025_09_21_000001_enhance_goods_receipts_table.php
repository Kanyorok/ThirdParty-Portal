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
        // Enhanced Goods Receipts table for comprehensive workflow
        Schema::table('t_GoodsReceipts', function (Blueprint $table) {
            // Additional PO matching fields
            $table->string('OrderLineID')->nullable()->after('ItemNo');
            $table->decimal('UnitPrice', 15, 2)->nullable()->after('ReceivedQTY');
            $table->decimal('TotalValue', 15, 2)->nullable()->after('UnitPrice');

            // Item type processing fields
            $table->string('ItemType', 50)->nullable()->after('TotalValue'); // Stock, Asset, Service
            $table->string('ProcessingStatus', 50)->default('pending')->after('ItemType'); // pending, processed, error

            // Stock-related fields
            $table->boolean('UpdatedStock')->default(false)->after('ProcessingStatus');
            $table->string('StockTransactionRef', 100)->nullable()->after('UpdatedStock');

            // Finance integration fields
            $table->boolean('CreatedJournalEntry')->default(false)->after('StockTransactionRef');
            $table->string('JournalEntryRef', 100)->nullable()->after('CreatedJournalEntry');
            $table->decimal('DebitAmount', 15, 2)->nullable()->after('JournalEntryRef');
            $table->decimal('CreditAmount', 15, 2)->nullable()->after('DebitAmount');

            // Asset register fields (for future implementation)
            $table->string('AssetRegisterRef', 100)->nullable()->after('CreditAmount');
            $table->boolean('RequiresAssetTagging')->default(false)->after('AssetRegisterRef');

            // Quality control and inspection
            $table->string('QualityStatus', 50)->default('pending')->after('InspectionStatus'); // pending, passed, failed, not_required
            $table->text('QualityRemarks')->nullable()->after('QualityStatus');
            $table->timestamp('QualityCheckedAt')->nullable()->after('QualityRemarks');
            $table->foreignId('QualityCheckedBy')->nullable()->constrained('t_Users', 'Id')->after('QualityCheckedAt');

            // Delivery and batch information
            $table->string('DeliveryNoteRef', 100)->nullable()->after('QualityCheckedBy');
            $table->string('BatchNumber', 100)->nullable()->after('DeliveryNoteRef');
            $table->date('ExpiryDate')->nullable()->after('BatchNumber');
            $table->date('ManufactureDate')->nullable()->after('ExpiryDate');

            // Additional tracking
            $table->text('ProcessingErrors')->nullable()->after('ManufactureDate');
            $table->timestamp('PostedAt')->nullable()->after('ProcessingErrors');
            $table->foreignId('PostedBy')->nullable()->constrained('t_Users', 'Id')->after('PostedAt');

            // Add indexes for performance
            $table->index(['GRNID', 'POID']);
            $table->index(['ProcessingStatus']);
            $table->index(['ItemType']);
            $table->index(['InspectionStatus']);
            $table->index(['QualityStatus']);
            $table->index(['ReceivedDate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_GoodsReceipts', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['GRNID', 'POID']);
            $table->dropIndex(['ProcessingStatus']);
            $table->dropIndex(['ItemType']);
            $table->dropIndex(['InspectionStatus']);
            $table->dropIndex(['QualityStatus']);
            $table->dropIndex(['ReceivedDate']);

            // Drop foreign key constraints
            $table->dropForeign(['QualityCheckedBy']);
            $table->dropForeign(['PostedBy']);

            // Drop columns
            $table->dropColumn([
                'OrderLineID', 'UnitPrice', 'TotalValue', 'ItemType', 'ProcessingStatus',
                'UpdatedStock', 'StockTransactionRef', 'CreatedJournalEntry', 'JournalEntryRef',
                'DebitAmount', 'CreditAmount', 'AssetRegisterRef', 'RequiresAssetTagging',
                'QualityStatus', 'QualityRemarks', 'QualityCheckedAt', 'QualityCheckedBy',
                'DeliveryNoteRef', 'BatchNumber', 'ExpiryDate', 'ManufactureDate',
                'ProcessingErrors', 'PostedAt', 'PostedBy'
            ]);
        });
    }
};
