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
        Schema::table('t_CompetitorStrategy', function (Blueprint $table) {
            $table->foreign(['CompetitorId'])->references(['CompetitorID'])->on('t_Competitors')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['StrategyId'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_CompetitorStrategy', function (Blueprint $table) {
            $table->dropForeign('t_competitorstrategy_competitorid_foreign');
            $table->dropForeign('t_competitorstrategy_createdby_foreign');
            $table->dropForeign('t_competitorstrategy_modifiedby_foreign');
            $table->dropForeign('t_competitorstrategy_strategyid_foreign');
        });
    }
};
