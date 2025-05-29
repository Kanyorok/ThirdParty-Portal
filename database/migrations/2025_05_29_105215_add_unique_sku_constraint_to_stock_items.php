<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_StockItems', function (Blueprint $table) {
           
            $table->unique(['SKUCode', 'Branch', 'Store'], 'sku_code_branch_store_unique');

        });
    }

    public function down(): void
    {
        Schema::table('t_StockItems', function (Blueprint $table) {
            $table->dropUnique('sku_code_branch_store_unique');
        });
    }
};
**/