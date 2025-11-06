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
        Schema::table('t_TripLogs', function (Blueprint $table) {
            $table->foreign(['ApprovedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LoadType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ParentTripID'])->references(['Id'])->on('t_TripLogs')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Status'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TripLogs', function (Blueprint $table) {
            $table->dropForeign('t_triplogs_approvedby_foreign');
            $table->dropForeign('t_triplogs_createdby_foreign');
            $table->dropForeign('t_triplogs_deletedby_foreign');
            $table->dropForeign('t_triplogs_loadtype_foreign');
            $table->dropForeign('t_triplogs_modifiedby_foreign');
            $table->dropForeign('t_triplogs_parenttripid_foreign');
            $table->dropForeign('t_triplogs_status_foreign');
            $table->dropForeign('t_triplogs_vehicletype_foreign');
        });
    }
};
