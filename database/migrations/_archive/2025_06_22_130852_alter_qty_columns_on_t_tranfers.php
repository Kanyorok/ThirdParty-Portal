<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_TransferItems', function (Blueprint $table) {
            $table->float('ApprovedQty', 15, 4)->change();
            $table->float('DispatchedQty', 15, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TransferItems', function (Blueprint $table) {
            $table->integer('ApprovedQty')->change();
            $table->integer('DispatchedQty')->change();
        });
    }
};
