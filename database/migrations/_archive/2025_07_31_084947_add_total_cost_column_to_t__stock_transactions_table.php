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
            // Adding TotalCost column to the t_StockTransactions table
            $table->decimal('TotalCost', 10, 2)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockTransactions', function (Blueprint $table) {
            $table->dropColumn('TotalCost');
        });
    }
};

   
