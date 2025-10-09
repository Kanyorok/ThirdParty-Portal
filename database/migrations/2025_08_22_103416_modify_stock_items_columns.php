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
        Schema::table('t_StockItems', function (Blueprint $table) {
            $table->boolean('Batch')->nullable()->change();
            $table->boolean('Serial')->nullable()->change();
            $table->boolean('Perishable')->nullable()->change();
            $table->boolean('Saleable')->nullable()->change();
            $table->boolean('Purchasable')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockItems', function (Blueprint $table) {
            $table->boolean('Batch')->nullable(false)->change();
            $table->boolean('Serial')->nullable(false)->change();
            $table->boolean('Perishable')->nullable(false)->change();
            $table->boolean('Saleable')->nullable(false)->change();
            $table->boolean('Purchasable')->nullable(false)->change();
        });
    }
};
