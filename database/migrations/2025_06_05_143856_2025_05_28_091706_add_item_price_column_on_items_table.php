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
        Schema::table('t_Items', function (Blueprint $table) {
            $table->foreignId('ItemPrice')->nullable()
                ->constrained('t_Pricing', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            $table->dropForeign(['ItemPrice']);
            $table->dropColumn('ItemPrice');
        });
    }
};
