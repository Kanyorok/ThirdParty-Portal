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
        Schema::create('t_SupplierCategory_ItemCategory', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('SupplierCategoryID');
            $table->bigInteger('ItemCategoryID');
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_suppli__3214ec07ae0b4c89');
            $table->unique(['SupplierCategoryID', 'ItemCategoryID'], 'uq_suppliercategory_itemcategory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SupplierCategory_ItemCategory');
    }
};
