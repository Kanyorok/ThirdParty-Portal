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
        Schema::table('t_Defects', function (Blueprint $table) {
            $table->foreign(['Condition'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Defect'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FromBranch'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InventoryHoldID'])->references(['Id'])->on('t_InventoryHold')->onUpdate('no action')->onDelete('no action');
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
        Schema::table('t_Defects', function (Blueprint $table) {
            $table->dropForeign('t_defects_condition_foreign');
            $table->dropForeign('t_defects_createdby_foreign');
            $table->dropForeign('t_defects_defect_foreign');
            $table->dropForeign('t_defects_deletedby_foreign');
            $table->dropForeign('t_defects_frombranch_foreign');
            $table->dropForeign('t_defects_inventoryholdid_foreign');
            $table->dropForeign('t_defects_itemid_foreign');
            $table->dropForeign('t_defects_modifiedby_foreign');
            $table->dropForeign('t_defects_store_foreign');
        });
    }
};
