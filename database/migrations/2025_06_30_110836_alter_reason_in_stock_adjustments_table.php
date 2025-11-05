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
        Schema::table('t_StockAdjustments', function (Blueprint $table) {
            $table->dropColumn('Reason');
        });

        Schema::table('t_StockAdjustments', function (Blueprint $table) {
            $table->foreignId('Reason')->nullable()
                ->constrained('t_CodeDetails', 'ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockAdjustments', function (Blueprint $table) {
            $table->dropForeign(['Reason']);
            $table->dropColumn('Reason');

            // Restore original column
            $table->string('Reason')->after('Branch');
        });
    }
};
