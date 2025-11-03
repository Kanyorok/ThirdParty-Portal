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
        Schema::table('t_ScheduleBoard', function (Blueprint $table) {
            $table->foreign(['BoardMemberId'])->references(['Id'])->on('t_BoardMembers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ScheduleId'])->references(['ScheduleID'])->on('t_Schedule')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ScheduleBoard', function (Blueprint $table) {
            $table->dropForeign('t_scheduleboard_boardmemberid_foreign');
            $table->dropForeign('t_scheduleboard_createdby_foreign');
            $table->dropForeign('t_scheduleboard_modifiedby_foreign');
            $table->dropForeign('t_scheduleboard_scheduleid_foreign');
        });
    }
};
