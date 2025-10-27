<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('t_ThirdParty_SupplierCategory')) {
            Schema::create('t_ThirdParty_SupplierCategory', function (Blueprint $table) {
                $table->unsignedBigInteger('third_party_id');
                $table->unsignedBigInteger('supplier_category_id');

                $table->primary(['third_party_id', 'supplier_category_id']);

                $table->foreign('third_party_id')
                    ->references('Id')->on('t_ThirdParties');
                $table->foreign('supplier_category_id')
                    ->references('SupplierCategoryID')->on('t_SupplierCategories');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ThirdParty_SupplierCategory');
    }
};


