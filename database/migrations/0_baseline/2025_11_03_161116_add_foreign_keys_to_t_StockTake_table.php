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
        Schema::table('t_StockTake', function (Blueprint $table) {
            $table->foreign(['BranchId'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['StoreId'])->references(['Id'])->on('t_Stores')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockTake', function (Blueprint $table) {
            $table->dropForeign('t_stocktake_branchid_foreign');
            $table->dropForeign('t_stocktake_createdby_foreign');
            $table->dropForeign('t_stocktake_deletedby_foreign');
            $table->dropForeign('t_stocktake_modifiedby_foreign');
            $table->dropForeign('t_stocktake_storeid_foreign');
        });
    }
};
