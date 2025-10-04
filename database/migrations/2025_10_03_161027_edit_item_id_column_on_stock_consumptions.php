<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_StockConsumptions', function (Blueprint $table) {
            // Drop old foreign key
            //$table->dropForeign(['ItemID']);

            // Add new foreign key to t_StockItems
        //     $table->foreign('ItemID')
        //           ->references('Id')
        //           ->on('t_StockItems')
        //           ->nullOnDelete(); // optional: set null if stock item deleted
        // });
    }

    public function down(): void
    {
        Schema::table('t_StockConsumptions', function (Blueprint $table) {
            // Rollback: drop new foreign key
            // $table->dropForeign(['ItemID']);

            // Restore old foreign key to t_Items
        //     $table->foreign('ItemID')
        //           ->references('Id')
        //           ->on('t_Items')
        //           ->nullOnDelete();
        // });
    }
};
