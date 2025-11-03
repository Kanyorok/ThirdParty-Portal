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
            if (Schema::hasColumn('t_StockAdjustments', 'Reason')) {
                try {
                    $table->dropForeign(['Reason']);
                } catch (\Exception $e) {
                }

                $table->dropColumn('Reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockAdjustments', function (Blueprint $table) {
            if (!Schema::hasColumn('t_StockAdjustments', 'Reason')) {
                $table->string('Reason')->nullable();
            }
        });
    }
};
