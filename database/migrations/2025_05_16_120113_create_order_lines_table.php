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
        Schema::create('t_OrderLines', function (Blueprint $table) {
            $table->id('Id');
            $table->bigInteger('iOrderID');
            $table->bigInteger('iOrigLineID')->nullable();
            $table->bigInteger('iGrvLineID')->nullable();
            $table->integer('iLineDocketMode')->nullable();
            $table->string('cDescription', 100)->nullable();
            $table->integer('iUnitsOfMeasureStockingID')->nullable();
            $table->integer('iUnitsOfMeasureCategoryID')->nullable();
            $table->integer('iUnitsOfMeasureID')->nullable();
            $table->float('fQuantity')->nullable();
            $table->float('fQtyChange')->nullable();
            $table->float('fQtyToProcess')->nullable();
            $table->float('fQtyLastProcess')->nullable();
            $table->float('fQtyProcessed')->nullable();
            $table->float('fQtyReserved')->nullable();
            $table->float('fQtyReservedChange')->nullable();
            $table->text('cLineNotes')->nullable();
            $table->float('fUnitPriceExcl')->nullable();
            $table->float('fUnitPriceIncl')->nullable();
            $table->integer('iUnitPriceOverrideReasonID')->nullable();
            $table->float('fUnitCost')->nullable();
            $table->float('fLineDiscount')->nullable();
            $table->integer('iLineDiscountReasonID')->nullable();
            $table->integer('iReturnReasonID')->nullable();
            $table->float('fTaxRate')->nullable();
            $table->boolean('bIsSerialItem')->nullable();
            $table->boolean('bIsWhseItem')->nullable();
            $table->float('fAddCost')->nullable();
            $table->string('cTradeinItem', 20)->nullable();
            $table->integer('iStockCodeID')->nullable();
            $table->integer('iJobID')->nullable();
            $table->integer('iWarehouseID')->nullable();
            $table->integer('iTaxTypeID')->nullable();
            $table->integer('iPriceListNameID')->nullable();
            $table->integer('iLineRepID')->nullable();
            $table->integer('iLineProjectID')->nullable();
            $table->integer('iLedgerAccountID')->nullable();
            $table->integer('iModule')->nullable();
            $table->boolean('bChargeCom')->nullable();
            $table->boolean('bIsLotItem')->nullable();
            $table->integer('iMFPID')->nullable();
            $table->integer('iLineID')->nullable();
            $table->bigInteger('iLinkedLineID')->nullable();
            $table->float('fQtyLinkedUsed')->nullable();
            $table->float('fUnitPriceInclOrig')->nullable();
            $table->float('fUnitPriceExclOrig')->nullable();
            $table->float('fUnitPriceInclForeignOrig')->nullable();
            $table->float('fUnitPriceExclForeignOrig')->nullable();
            $table->integer('iDeliveryMethodID')->nullable();
            $table->float('fQtyDeliver')->nullable();
            $table->dateTime('dDeliveryDate')->nullable();
            $table->integer('iDeliveryStatus')->nullable();
            $table->float('fQtyForDelivery')->nullable();
            $table->string('Terms')->nullable();
            $table->string('Priority')->nullable();
            $table->string('BranchID')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_OrderLines');
    }
};
