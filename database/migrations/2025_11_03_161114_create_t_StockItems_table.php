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
        Schema::create('t_StockItems', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('SKUCode')->nullable();
            $table->bigInteger('ItemID');
            $table->boolean('Batch')->nullable();
            $table->boolean('Serial')->nullable();
            $table->boolean('Perishable')->nullable();
            $table->boolean('Saleable')->nullable();
            $table->boolean('Purchasable')->nullable();
            $table->bigInteger('Store')->nullable();
            $table->bigInteger('Branch');
            $table->integer('CurrentQty');
            $table->string('Min');
            $table->integer('Reorder');
            $table->integer('Max');
            $table->dateTime('LastReceived');
            $table->boolean('Status');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->decimal('UnitCost', 10)->nullable();
            $table->integer('UOM')->nullable();

            $table->primary(['Id'], 'pk__t_stocki__3214ec070ade28a8');
            $table->unique(['SKUCode', 'Branch', 'Store'], 'sku_code_branch_store_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_StockItems');
    }
};
