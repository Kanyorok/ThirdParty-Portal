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
        Schema::create('t_StockTransactions', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('SKUID')->nullable();
            $table->bigInteger('TransactionType')->nullable();
            $table->string('ReferenceID')->nullable();
            $table->bigInteger('ItemID');
            $table->bigInteger('StoreID')->nullable();
            $table->bigInteger('BranchID');
            $table->decimal('UnitCost', 10)->nullable();
            $table->bigInteger('UOMID');
            $table->decimal('QuantityIn', 18)->nullable()->default(0);
            $table->decimal('QuantityOut', 18)->nullable()->default(0);
            $table->decimal('BalanceQty', 18)->nullable()->default(0);
            $table->dateTime('TransactionDate');
            $table->text('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->decimal('TotalCost', 18)->nullable();

            $table->primary(['Id'], 'pk__t_stockt__3214ec07e821b8f5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_StockTransactions');
    }
};
