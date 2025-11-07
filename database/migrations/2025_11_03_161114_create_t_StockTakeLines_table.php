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
        Schema::create('t_StockTakeLines', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('StockTakeId');
            $table->bigInteger('ItemId');
            $table->integer('ActualQuantity');
            $table->integer('CountedQuantity');
            $table->string('Remarks');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_stockt__3214ec0755b251d0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_StockTakeLines');
    }
};
