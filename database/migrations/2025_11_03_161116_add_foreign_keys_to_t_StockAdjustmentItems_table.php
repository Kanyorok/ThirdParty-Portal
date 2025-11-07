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
        Schema::table('t_StockAdjustmentItems', function (Blueprint $table) {
            $table->foreign(['AdjustmentId'])->references(['Id'])->on('t_StockAdjustments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Item'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Reason'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockAdjustmentItems', function (Blueprint $table) {
            $table->dropForeign('t_stockadjustmentitems_adjustmentid_foreign');
            $table->dropForeign('t_stockadjustmentitems_createdby_foreign');
            $table->dropForeign('t_stockadjustmentitems_deletedby_foreign');
            $table->dropForeign('t_stockadjustmentitems_item_foreign');
            $table->dropForeign('t_stockadjustmentitems_modifiedby_foreign');
            $table->dropForeign('t_stockadjustmentitems_reason_foreign');
        });
    }
};
