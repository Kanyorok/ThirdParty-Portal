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
        Schema::table('t_SupplierCategory_ItemCategory', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemCategoryID'])->references(['Id'])->on('t_ItemCategories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SupplierCategoryID'])->references(['SupplierCategoryID'])->on('t_SupplierCategories')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_SupplierCategory_ItemCategory', function (Blueprint $table) {
            $table->dropForeign('t_suppliercategory_itemcategory_createdby_foreign');
            $table->dropForeign('t_suppliercategory_itemcategory_deletedby_foreign');
            $table->dropForeign('t_suppliercategory_itemcategory_itemcategoryid_foreign');
            $table->dropForeign('t_suppliercategory_itemcategory_modifiedby_foreign');
            $table->dropForeign('t_suppliercategory_itemcategory_suppliercategoryid_foreign');
        });
    }
};
