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
        Schema::table('t_RFQLines', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemCategoryId'])->references(['Id'])->on('t_ItemCategories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemId'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RequisitionId'])->references(['Id'])->on('t_Requisitions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['RequisitionLineId'])->references(['Id'])->on('t_RequisitionLines')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['RFQId'])->references(['Id'])->on('t_RFQ')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQLines', function (Blueprint $table) {
            $table->dropForeign('t_rfqlines_createdby_foreign');
            $table->dropForeign('t_rfqlines_deletedby_foreign');
            $table->dropForeign('t_rfqlines_itemcategoryid_foreign');
            $table->dropForeign('t_rfqlines_itemid_foreign');
            $table->dropForeign('t_rfqlines_modifiedby_foreign');
            $table->dropForeign('t_rfqlines_requisitionid_foreign');
            $table->dropForeign('t_rfqlines_requisitionlineid_foreign');
            $table->dropForeign('t_rfqlines_rfqid_foreign');
        });
    }
};
