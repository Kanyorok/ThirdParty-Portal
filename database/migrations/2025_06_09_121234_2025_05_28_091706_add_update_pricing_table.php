<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('t_Pricing', function (Blueprint $table) {
            $table->string('PriceID')->nullable()->change();
            $table->dropForeign(['UOM']);
            $table->foreign('UOM')->references('Id')->on('t_UOM');
        });
    }

    public function down(): void
    {
        Schema::table('t_Pricing', function (Blueprint $table) {
            $table->string('PriceID')->nullable(false)->change();
            $table->dropForeign(['UOM']);
            $table->foreign('UOM')->references('Id')->on('t_Items');
        });
    }
};