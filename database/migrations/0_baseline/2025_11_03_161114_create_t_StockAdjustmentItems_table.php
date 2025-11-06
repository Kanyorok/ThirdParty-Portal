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
        Schema::create('t_StockAdjustmentItems', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('AdjustmentId');
            $table->bigInteger('Item');
            $table->integer('AdjustmentQty');
            $table->string('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->decimal('UnitCost', 10)->nullable();
            $table->integer('UOM')->nullable();
            $table->bigInteger('Reason')->nullable();

            $table->primary(['Id'], 'pk__t_stocka__3214ec07830aec57');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_StockAdjustmentItems');
    }
};
