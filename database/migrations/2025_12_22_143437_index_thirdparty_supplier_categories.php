<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('t_ThirdParty_SupplierCategory')) {
            Schema::create('t_ThirdParty_SupplierCategory', function (Blueprint $blueprint) {
                $blueprint->bigInteger('third_party_id');
                $blueprint->bigInteger('supplier_category_id');

                $blueprint->primary(['third_party_id', 'supplier_category_id'], 'pk_thirdparty_category');

                $blueprint->foreign('third_party_id')
                    ->references('Id')
                    ->on('t_ThirdParties')
                    ->onDelete('cascade');

                $blueprint->foreign('supplier_category_id')
                    ->references('SupplierCategoryID')
                    ->on('t_SupplierCategories')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('t_ThirdParty_SupplierCategory');
    }
};
