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
        Schema::table('t_StockTransactions', function (Blueprint $table) {
            // Alter TotalCost column to decimal(18,2)
            $table->decimal('TotalCost', 18, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockTransactions', function (Blueprint $table) {
            // Revert TotalCost column back to decimal(10,2)
            $table->decimal('TotalCost', 10, 2)->nullable()->change();
        });
    }
};
