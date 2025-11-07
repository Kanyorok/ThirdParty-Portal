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
        Schema::table('t_PettyCashLines', function (Blueprint $table) {
            $table->foreign(['VoucherID'])->references(['VoucherID'])->on('t_PettyCashVouchers')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PettyCashLines', function (Blueprint $table) {
            $table->dropForeign('t_pettycashlines_voucherid_foreign');
        });
    }
};
