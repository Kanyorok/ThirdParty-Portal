<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_StockAdjustmentItems', function (Blueprint $table) {
            // Adding UnitCost column to the t_StockAdjustmentItems table
            $table->decimal('UnitCost', 10, 2)->nullable();
            $table->integer('UOM')->after('UnitCost')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockAdjustmentItems', function (Blueprint $table) {
            $table->dropColumn('UnitCost');
            $table->dropColumn('UOM');
        });
    }
};
