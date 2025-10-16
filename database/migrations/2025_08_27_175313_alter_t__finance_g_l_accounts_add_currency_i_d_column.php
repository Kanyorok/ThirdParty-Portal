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
        Schema::table('t_FinanceGLAccounts', function (Blueprint $table) {
            $table->integer('CurrencyID')->default(56);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceGLAccounts', function (Blueprint $table) {
            $table->dropColumn('CurrencyID');
        });
    }
};
