<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateStockItemsUniqueIndexes extends Migration
{
    public function up()
    {
        // Drop old unique index on SKUCode using raw SQL
        DB::statement('DROP INDEX t_stockitems_skucode_unique ON dbo.t_StockItems');

        // Add new composite unique index
        Schema::table('t_StockItems', function (Blueprint $table) {
            $table->unique(['SKUCode', 'Branch', 'Store'], 'sku_code_branch_store_unique');
        });
    }

    public function down()
    {
        Schema::table('t_StockItems', function (Blueprint $table) {
            $table->dropUnique('sku_code_branch_store_unique');
            $table->unique('SKUCode', 't_stockitems_skucode_unique');
        });
    }
}
