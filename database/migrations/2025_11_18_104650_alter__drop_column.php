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
        Schema::table('t_WorkFlowStages', function (Blueprint $table) {
            //
            // Drop foreign key
            $table->dropForeign('t_WorkFlowStages_WorkFlowLimitId_foreign');
            $table->dropColumn('WorkFlowLimitId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_WorkFlowStages', function (Blueprint $table) {
            //
            $table->foreignId('WorkFlowLimitId')
                ->nullable()
                ->constrained('t_WorkFlowLimits', 'Id');
        });
    }
};
