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
        Schema::table('t_GoodsReceipts', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PostedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['QualityCheckedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SupplierId'])->references(['Id'])->on('t_Suppliers')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_GoodsReceipts', function (Blueprint $table) {
            $table->dropForeign('t_goodsreceipts_createdby_foreign');
            $table->dropForeign('t_goodsreceipts_deletedby_foreign');
            $table->dropForeign('t_goodsreceipts_modifiedby_foreign');
            $table->dropForeign('t_goodsreceipts_postedby_foreign');
            $table->dropForeign('t_goodsreceipts_qualitycheckedby_foreign');
            $table->dropForeign('t_goodsreceipts_supplierid_foreign');
        });
    }
};
