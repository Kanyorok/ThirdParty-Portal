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
        Schema::table('t_StockItems', function (Blueprint $table) {
            $table->foreign(['Branch'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemID'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Store'])->references(['Id'])->on('t_Stores')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockItems', function (Blueprint $table) {
            $table->dropForeign('t_stockitems_branch_foreign');
            $table->dropForeign('t_stockitems_createdby_foreign');
            $table->dropForeign('t_stockitems_deletedby_foreign');
            $table->dropForeign('t_stockitems_itemid_foreign');
            $table->dropForeign('t_stockitems_modifiedby_foreign');
            $table->dropForeign('t_stockitems_store_foreign');
        });
    }
};
