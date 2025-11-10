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
        Schema::table('t_MarketingPlannerActivities', static function (Blueprint $table) {
            $table->longText('Materials')->nullable()->after('Notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MarketingPlannerActivities', function (Blueprint $table) {
            $table->dropColumn('Materials');
        });
    }
};
