<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_StockConsumptions', function (Blueprint $table) {
            $table->id('Id');
            $table->string('ConsumptionNo')->nullable()->unique();
            $table->foreignId('ItemID')->nullable()->constrained('t_Items', 'Id');
            $table->foreignId('UOM')->constrained('t_UOM', 'Id');
            $table->decimal('Quantity', 10, 2);
            //$table->foreignId('SKUID')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->foreignId('StoreID')->nullable()->constrained('t_Stores', 'Id');
            $table->foreignId('BranchID')->constrained('t_Branches', 'Id');
            $table->foreignId('IssuedToType')->constrained('t_CodeDetails', 'ID');
            $table->integer('IssuedToID');
            $table->foreignId('IssuedBy')->constrained('t_Users', 'Id');
            $table->dateTime('IssuedOn');
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
        Schema::dropIfExists('t_StockConsumptions');
    }
};
