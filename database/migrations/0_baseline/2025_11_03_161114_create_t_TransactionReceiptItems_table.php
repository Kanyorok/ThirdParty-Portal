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
        Schema::create('t_TransactionReceiptItems', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ReceiptId');
            $table->bigInteger('Item');
            $table->string('DispatchedQty');
            $table->string('ReceivedQty')->nullable();
            $table->string('DamagedQty')->nullable();
            $table->string('Discrepancy');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('Store')->nullable();
            $table->decimal('UnitCost', 10)->nullable();
            $table->integer('UOM')->nullable();

            $table->primary(['Id'], 'pk__t_transa__3214ec07120ee3af');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TransactionReceiptItems');
    }
};
