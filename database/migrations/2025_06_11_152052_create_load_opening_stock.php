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
        Schema::create('t_LoadOpeningStock', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('BranchId')->constratined('t_Branches','Id');
            $table->foreignId('StoreId')->constratined('t_Stores','Id');
            $table->foreignId('ItemCode')->constratined('t_Items','ItemCode');
            $table->date('Date');
            $table->integer('Quantity');
            $table->foreignId('UOM')->constratined('t_Items','UOM');
            $table->float('Value');
            $table->string('Remarks');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
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
        Schema::dropIfExists('t_LoadOpeningStock');
    }
};
