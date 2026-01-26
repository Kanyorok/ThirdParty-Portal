<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_StockGRNLedger', function (Blueprint $table) {
            $table->id('Id');
            $table->string('GRNID');
            $table->foreignId('GoodsReceiptId')->constrained('t_GoodsReceipts', 'Id')->nullable();
            $table->foreignId('StockItemId')->constrained('t_StockItems', 'Id')->nullable();
            $table->foreignId('ItemNo')->constrained('t_Items', 'Id')->nullable();
            $table->string('SKUCode');
            $table->decimal('ReceivedQTY', 15, 3);
            $table->decimal('RemainingQTY', 15, 3)->default(0);
            $table->decimal('UnitPrice', 15, 2);
            $table->foreignId('Store')->constrained('t_Stores', 'Id')->nullable();
            $table->foreignId('Branch')->constrained('t_Branches', 'Id')->nullable();
            $table->date('ReceivedDate');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->timestamp('DeletedOn')->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_StockGRNLedger');
    }
};