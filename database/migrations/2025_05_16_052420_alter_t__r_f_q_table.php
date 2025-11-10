<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTRFQTable extends Migration
{
    public function up()
    {
        Schema::table('t_RFQ', function (Blueprint $table) {
            // Drop foreign key and column for ItemCategoryId
            $table->dropForeign(['ItemCategoryId']);
            $table->dropColumn('ItemCategoryId');

            // Drop RequisitionItems JSON column
            $table->dropColumn('RequisitionItems');

            // Add new RequisitionId column
            $table->foreignId('RequisitionId')->constrained('t_Requisitions'); // adjust table name if different
        });
    }

    public function down()
    {
        Schema::table('t_RFQ', function (Blueprint $table) {
            // Rollback: remove new RequisitionId column
            $table->dropForeign(['RequisitionId']);
            $table->dropColumn('RequisitionId');

            // Re-add dropped columns
            $table->foreignId('ItemCategoryId')->constrained('t_ItemCategories');
            $table->json('RequisitionItems')->nullable();
        });
    }
}
