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
        Schema::table('t_ScheduleUsers', static function (Blueprint $table) {
            $table->dateTime('ReminderOn')->nullable()->after('ModifiedOn');
        });
        Schema::table('t_ScheduleClients', static function (Blueprint $table) {
            $table->dateTime('ReminderOn')->nullable()->after('ModifiedOn');
        });
        Schema::table('t_ScheduleLeads', static function (Blueprint $table) {
            $table->dateTime('ReminderOn')->nullable()->after('ModifiedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ScheduleUsers', static function (Blueprint $table) {
            $table->dropColumn('ReminderOn');
        });
        Schema::table('t_ScheduleClients', static function (Blueprint $table) {
            $table->dropColumn('ReminderOn');
        });
        Schema::table('t_ScheduleLeads', static function (Blueprint $table) {
            $table->dropColumn('ReminderOn');
        });
    }
};
