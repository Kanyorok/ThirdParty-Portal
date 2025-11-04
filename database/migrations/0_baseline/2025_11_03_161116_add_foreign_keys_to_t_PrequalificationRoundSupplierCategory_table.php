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
        Schema::table('t_PrequalificationRoundSupplierCategory', function (Blueprint $table) {
            $table->foreign(['RoundID'])->references(['RoundID'])->on('t_PrequalificationRounds')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['SupplierCategoryID'])->references(['SupplierCategoryID'])->on('t_SupplierCategories')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ThirdPartyID'])->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PrequalificationRoundSupplierCategory', function (Blueprint $table) {
            $table->dropForeign('t_prequalificationroundsuppliercategory_roundid_foreign');
            $table->dropForeign('t_prequalificationroundsuppliercategory_suppliercategoryid_foreign');
            $table->dropForeign('t_prequalificationroundsuppliercategory_thirdpartyid_foreign');
        });
    }
};
