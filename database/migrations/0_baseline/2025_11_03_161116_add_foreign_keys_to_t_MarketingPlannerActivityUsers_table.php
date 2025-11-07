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
        Schema::table('t_MarketingPlannerActivityUsers', function (Blueprint $table) {
            $table->foreign(['ActivityId'])->references(['Id'])->on('t_MarketingPlannerActivities')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UserID'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MarketingPlannerActivityUsers', function (Blueprint $table) {
            $table->dropForeign('t_marketingplanneractivityusers_activityid_foreign');
            $table->dropForeign('t_marketingplanneractivityusers_createdby_foreign');
            $table->dropForeign('t_marketingplanneractivityusers_deletedby_foreign');
            $table->dropForeign('t_marketingplanneractivityusers_modifiedby_foreign');
            $table->dropForeign('t_marketingplanneractivityusers_userid_foreign');
        });
    }
};
