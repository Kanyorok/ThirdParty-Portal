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
        Schema::table('t_Meetings', static function (Blueprint $table) {
            $table->char('MeetingLocationType', 2)->default(\App\Enums\Schedule\MeetingLocationEnum::Physical->value)->after('Type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Meetings', static function (Blueprint $table) {
            $table->dropColumn('MeetingLocationType');
        });
    }
};
