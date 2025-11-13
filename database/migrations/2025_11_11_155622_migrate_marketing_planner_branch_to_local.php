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
        Schema::table('t_MarketingPlanner', static function (Blueprint $table) {
            $table->dropIndex(['BranchId']);
            $table->dropColumn('BranchId');
        });

        Schema::table('t_MarketingPlanner', static function (Blueprint $table) {
            $table->foreignId('BranchId')->constrained('t_Branches', 'Id');
        });

        Schema::table('t_MarketingPlannerActivities', static function (Blueprint $table) {
            $table->dropIndex(['BranchId']);
            $table->dropColumn('BranchId');
        });

        Schema::table('t_MarketingPlannerActivities', static function (Blueprint $table) {
            $table->foreignId('BranchId')->constrained('t_Branches', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MarketingPlanner', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('BranchId');
        });

        Schema::table('t_MarketingPlanner', static function (Blueprint $table) {
            $table->char('BranchId', '5')->nullable()->index();
        });

        Schema::table('t_MarketingPlannerActivities', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('BranchId');
        });

        Schema::table('t_MarketingPlannerActivities', static function (Blueprint $table) {
            $table->char('BranchId', '5')->nullable()->index();
        });
    }
};
