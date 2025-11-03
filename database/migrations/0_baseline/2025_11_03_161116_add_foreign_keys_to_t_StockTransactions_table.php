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
        Schema::table('t_StockTransactions', function (Blueprint $table) {
            $table->foreign(['BranchID'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ItemID'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['StoreID'])->references(['Id'])->on('t_Stores')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TransactionType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UOMID'])->references(['Id'])->on('t_UOM')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockTransactions', function (Blueprint $table) {
            $table->dropForeign('t_stocktransactions_branchid_foreign');
            $table->dropForeign('t_stocktransactions_createdby_foreign');
            $table->dropForeign('t_stocktransactions_deletedby_foreign');
            $table->dropForeign('t_stocktransactions_itemid_foreign');
            $table->dropForeign('t_stocktransactions_modifiedby_foreign');
            $table->dropForeign('t_stocktransactions_storeid_foreign');
            $table->dropForeign('t_stocktransactions_transactiontype_foreign');
            $table->dropForeign('t_stocktransactions_uomid_foreign');
        });
    }
};
