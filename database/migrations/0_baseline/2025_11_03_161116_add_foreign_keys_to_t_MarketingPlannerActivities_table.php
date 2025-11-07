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
        Schema::table('t_MarketingPlannerActivities', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['MasterPlannerId'])->references(['Id'])->on('t_MarketingPlanner')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PlannerId'])->references(['Id'])->on('t_MarketingPlanner')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MarketingPlannerActivities', function (Blueprint $table) {
            $table->dropForeign('t_marketingplanneractivities_createdby_foreign');
            $table->dropForeign('t_marketingplanneractivities_deletedby_foreign');
            $table->dropForeign('t_marketingplanneractivities_masterplannerid_foreign');
            $table->dropForeign('t_marketingplanneractivities_modifiedby_foreign');
            $table->dropForeign('t_marketingplanneractivities_plannerid_foreign');
        });
    }
};
