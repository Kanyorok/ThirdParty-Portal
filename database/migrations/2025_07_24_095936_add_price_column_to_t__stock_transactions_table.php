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
        Schema::table('t_TransferItems', function (Blueprint $table) {
            $table->decimal('UnitCost', 10, 2)->after('DispatchedQty')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TransferItems', function (Blueprint $table) {
            $table->dropColumn('UnitCost');
        });
    }
};
