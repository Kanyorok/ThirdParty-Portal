<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_SupplierCategory_ItemCategory', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('SupplierCategoryID')->constrained('t_SupplierCategories', 'SupplierCategoryID')->cascadeOnDelete();
            $table->foreignId('ItemCategoryID')->constrained('t_ItemCategories', 'Id')->cascadeOnDelete();
            $table->unique(['SupplierCategoryID', 'ItemCategoryID'], 'uq_suppliercategory_itemcategory');
            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_SupplierCategory_ItemCategory');
    }
};
