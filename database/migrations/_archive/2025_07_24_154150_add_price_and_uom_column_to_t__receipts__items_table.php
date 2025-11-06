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
        Schema::table('t_TransactionReceiptItems', function (Blueprint $table) {
            // Adding UnitCost column to the t_TransactionReceiptItems table
            $table->decimal('UnitCost', 10, 2)->after('DispatchedQty')->nullable();
            $table->integer('UOM')->after('UnitCost')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TransactionReceiptItems', function (Blueprint $table) {
            $table->dropColumn('UnitCost');
            $table->dropColumn('UOM');
        });
    }
};
