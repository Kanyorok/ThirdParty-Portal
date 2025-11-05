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
        Schema::table('t_MarketingPlanner', function (Blueprint $table) {
            $table->foreign(['ArchivedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['MasterPlannerId'])->references(['Id'])->on('t_MarketingPlanner')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Modes'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['OwnerId'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MarketingPlanner', function (Blueprint $table) {
            $table->dropForeign('t_marketingplanner_archivedby_foreign');
            $table->dropForeign('t_marketingplanner_createdby_foreign');
            $table->dropForeign('t_marketingplanner_deletedby_foreign');
            $table->dropForeign('t_marketingplanner_masterplannerid_foreign');
            $table->dropForeign('t_marketingplanner_modes_foreign');
            $table->dropForeign('t_marketingplanner_modifiedby_foreign');
            $table->dropForeign('t_marketingplanner_ownerid_foreign');
        });
    }
};
