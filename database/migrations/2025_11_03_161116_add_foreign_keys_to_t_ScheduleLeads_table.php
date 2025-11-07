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
        Schema::table('t_ScheduleLeads', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LeadId'])->references(['LeadID'])->on('t_Leads')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ScheduleId'])->references(['ScheduleID'])->on('t_Schedule')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ScheduleLeads', function (Blueprint $table) {
            $table->dropForeign('t_scheduleleads_createdby_foreign');
            $table->dropForeign('t_scheduleleads_leadid_foreign');
            $table->dropForeign('t_scheduleleads_modifiedby_foreign');
            $table->dropForeign('t_scheduleleads_scheduleid_foreign');
        });
    }
};
