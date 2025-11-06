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
            // Drop the old string column
            $table->dropColumn('AdjustedBy');
        });

        Schema::table('t_StockAdjustments', function (Blueprint $table) {
            // Add the new nullable foreign key column
            $table->foreignId('AdjustedBy')
                ->nullable()
                ->after('Reason')
                ->constrained('t_Users', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_StockAdjustments', function (Blueprint $table) {
            $table->dropForeign(['AdjustedBy']);
            $table->dropColumn('AdjustedBy');

            // Restore the original string column
            $table->string('AdjustedBy')->after('Reason');
        });
    }
};
