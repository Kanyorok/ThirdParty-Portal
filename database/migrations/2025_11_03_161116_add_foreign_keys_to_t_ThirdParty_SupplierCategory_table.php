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
        Schema::table('t_ThirdParty_SupplierCategory', function (Blueprint $table) {
            $table->foreign(['supplier_category_id'])->references(['SupplierCategoryID'])->on('t_SupplierCategories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['third_party_id'])->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdParty_SupplierCategory', function (Blueprint $table) {
            $table->dropForeign('t_thirdparty_suppliercategory_supplier_category_id_foreign');
            $table->dropForeign('t_thirdparty_suppliercategory_third_party_id_foreign');
        });
    }
};
