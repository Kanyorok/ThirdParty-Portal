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
        Schema::create('t_StockConsumptions', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ConsumptionNo')->nullable()->unique();
            $table->bigInteger('ItemID')->nullable();
            $table->bigInteger('UOM');
            $table->decimal('Quantity', 10);
            $table->bigInteger('StoreID')->nullable();
            $table->bigInteger('BranchID');
            $table->bigInteger('IssuedToType');
            $table->integer('IssuedToID');
            $table->bigInteger('IssuedBy');
            $table->dateTime('IssuedOn');
            $table->text('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_stockc__3214ec07190bb04f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_StockConsumptions');
    }
};
