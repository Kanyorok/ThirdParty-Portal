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
            $table->id();
            $table->string('GRNID')->default(0);
            $table->string('POID')->default(0);
<<<<<<< HEAD:database/migrations/2025_05_09_090620_create_goods_receipts_table.php
            //$table->foreignId('SupplierID')->constrained('t_Supplier', 'Id');
=======
            $table->foreignId('SupplierID')->constrained('t_Suppliers', 'Id');
>>>>>>> dev:database/migrations/2025_05_08_091651_create_goods_receipt.php
            $table->dateTime('ReceivedDate');
            $table->string('StoreID')->nullable();
            $table->string('ReceivedBy')->nullable();
            $table->string('InspectionStatus')->nullable();
            $table->string('TransferStatus')->nullable();
            $table->string('ItemNo')->nullable();
            $table->decimal('POQTY')->nullable();
            $table->decimal('ReceivedQTY')->nullable();
            $table->string('TransferTo')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn');
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
