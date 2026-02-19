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
        if (! Schema::hasColumn('t_WorkFlowStages', 'IsDocRequired')) {
            Schema::table('t_WorkFlowStages', function (Blueprint $table) {
                $table->boolean('IsDocRequired')->default(false)->after('StatusId');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('t_WorkFlowStages', 'IsDocRequired')) {
            Schema::table('t_WorkFlowStages', function (Blueprint $table) {
                $table->dropColumn('IsDocRequired');
            });
        }
    }
};
