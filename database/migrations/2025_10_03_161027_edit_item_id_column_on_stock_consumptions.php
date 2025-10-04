<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the existing table: t_StockConsumptions
        Schema::table('t_StockConsumptions', function (Blueprint $table) {

            // 🔹 Step 1: Drop the old foreign key referencing t_Items
            // (This is commented out for now. Uncomment when ready to run.)
            // $table->dropForeign(['ItemID']);

            // 🔹 Step 2: Add new foreign key referencing t_StockItems(Id)
            // (This enforces referential integrity and sets NULL when the referenced item is deleted.)
            // $table->foreign('ItemID')
            //     ->references('Id')
            //     ->on('t_StockItems')
            //     ->nullOnDelete(); // optional: set null if the stock item is deleted
        });
    }

    public function down(): void
    {
        // Rollback logic: revert to the old reference on t_Items
        Schema::table('t_StockConsumptions', function (Blueprint $table) {

            // 🔹 Step 1: Drop the new foreign key referencing t_StockItems
            // (Commented out until you’re ready to enable the migration.)
            // $table->dropForeign(['ItemID']);

            // 🔹 Step 2: Restore the original foreign key referencing t_Items(Id)
            // $table->foreign('ItemID')
            //     ->references('Id')
            //     ->on('t_Items')
            //     ->nullOnDelete(); // restore previous delete behavior
        });
    }
};
