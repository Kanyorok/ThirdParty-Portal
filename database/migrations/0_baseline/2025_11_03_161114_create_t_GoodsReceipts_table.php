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
        Schema::create('t_GoodsReceipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('GRNID')->default('0');
            $table->string('POID')->default('0');
            $table->bigInteger('SupplierId');
            $table->dateTime('ReceivedDate')->index();
            $table->string('StoreID')->nullable();
            $table->string('ReceivedBy')->nullable();
            $table->string('InspectionStatus')->nullable()->index();
            $table->string('TransferStatus')->nullable();
            $table->string('ItemNo')->nullable();
            $table->decimal('POQTY')->nullable();
            $table->decimal('ReceivedQTY')->nullable();
            $table->string('TransferTo')->nullable();
            $table->boolean('TagRequired')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('OrderLineID')->nullable();
            $table->decimal('UnitPrice', 15)->nullable();
            $table->decimal('TotalValue', 15)->nullable();
            $table->string('ItemType', 50)->nullable()->index();
            $table->string('ProcessingStatus', 50)->default('pending')->index();
            $table->boolean('UpdatedStock')->default(false);
            $table->string('StockTransactionRef', 100)->nullable();
            $table->boolean('CreatedJournalEntry')->default(false);
            $table->string('JournalEntryRef', 100)->nullable();
            $table->decimal('DebitAmount', 15)->nullable();
            $table->decimal('CreditAmount', 15)->nullable();
            $table->string('AssetRegisterRef', 100)->nullable();
            $table->boolean('RequiresAssetTagging')->default(false);
            $table->string('QualityStatus', 50)->default('pending')->index();
            $table->text('QualityRemarks')->nullable();
            $table->dateTime('QualityCheckedAt')->nullable();
            $table->bigInteger('QualityCheckedBy')->nullable();
            $table->string('DeliveryNoteRef', 100)->nullable();
            $table->string('BatchNumber', 100)->nullable();
            $table->date('ExpiryDate')->nullable();
            $table->date('ManufactureDate')->nullable();
            $table->text('ProcessingErrors')->nullable();
            $table->dateTime('PostedAt')->nullable();
            $table->bigInteger('PostedBy')->nullable();

            $table->primary(['id'], 'pk__t_goodsr__3213e83f4205e9eb');
            $table->index(['GRNID', 'POID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_GoodsReceipts');
    }
};
