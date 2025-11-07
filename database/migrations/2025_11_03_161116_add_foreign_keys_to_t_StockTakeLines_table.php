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
        Schema::table('t_StockTakeLines', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemId'])->references(['Id'])->on('t_StockItems')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['StockTakeId'])->references(['Id'])->on('t_StockTake')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockTakeLines', function (Blueprint $table) {
            $table->dropForeign('t_stocktakelines_createdby_foreign');
            $table->dropForeign('t_stocktakelines_deletedby_foreign');
            $table->dropForeign('t_stocktakelines_itemid_foreign');
            $table->dropForeign('t_stocktakelines_modifiedby_foreign');
            $table->dropForeign('t_stocktakelines_stocktakeid_foreign');
        });
    }
};
