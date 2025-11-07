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
        Schema::table('t_Items', function (Blueprint $table) {
            $table->foreign(['Category'])->references(['Id'])->on('t_ItemCategories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InventoryType'])->references(['Id'])->on('t_InventoryTypes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemPrice'])->references(['Id'])->on('t_Pricing')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemType'])->references(['Id'])->on('t_ItemTypes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Status'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UOM'])->references(['Id'])->on('t_UOM')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            $table->dropForeign('t_items_category_foreign');
            $table->dropForeign('t_items_createdby_foreign');
            $table->dropForeign('t_items_deletedby_foreign');
            $table->dropForeign('t_items_inventorytype_foreign');
            $table->dropForeign('t_items_itemprice_foreign');
            $table->dropForeign('t_items_itemtype_foreign');
            $table->dropForeign('t_items_modifiedby_foreign');
            $table->dropForeign('t_items_status_foreign');
            $table->dropForeign('t_items_uom_foreign');
        });
    }
};
