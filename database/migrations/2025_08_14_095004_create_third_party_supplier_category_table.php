<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_ThirdParty_SupplierCategory', function (Blueprint $table) {
            $table->unsignedBigInteger('third_party_id');
            $table->unsignedBigInteger('supplier_category_id');

            $table->primary(['third_party_id', 'supplier_category_id']);

            $table->foreign('third_party_id')->references('id')->on('t_ThirdParties')->onDelete('cascade');
            $table->foreign('supplier_category_id')->references('SupplierCategoryID')->on('t_SupplierCategories')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_ThirdParty_SupplierCategory');
    }
};
