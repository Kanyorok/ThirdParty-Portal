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
            $table->id('Id');
            $table->string('SKUID')->nullable();
            $table->foreignId('TransactionType')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->string('ReferenceID')->nullable();
            $table->foreignId('ItemID')->constrained('t_Items', 'Id');
            $table->foreignId('StoreID')->nullable()->constrained('t_Stores', 'Id');
            $table->foreignId('BranchID')->constrained('t_Branches', 'Id');
            $table->decimal('UnitCost', 10, 2)->nullable();  
            $table->foreignId('UOMID')->constrained('t_UOM', 'Id');
            $table->decimal('QuantityIn', 18, 2)->nullable()->default(0);
            $table->decimal('QuantityOut', 18, 2)->nullable()->default(0);
            $table->decimal('BalanceQty', 18, 2)->nullable() ->default(0);
            $table->dateTime('TransactionDate');
            $table->text('Remarks')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
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
        Schema::dropIfExists('t_StockTransactions');
    }
};
