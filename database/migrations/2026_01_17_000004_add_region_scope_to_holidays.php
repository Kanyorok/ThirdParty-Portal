<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRHolidays', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRHolidays', 'RegionScope')) {
                $table->string('RegionScope', 20)->default('Global')->after('Region');
            }
            if (!Schema::hasColumn('t_HRHolidays', 'CountryId')) {
                $table->unsignedBigInteger('CountryId')->nullable()->after('RegionScope');
            }
        });

        Schema::table('t_HRHolidays', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRHolidays', 'CountryId') && Schema::hasTable('t_Countries')) {
                $table->foreign('CountryId')->references('Id')->on('t_Countries');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRHolidays', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRHolidays', 'CountryId')) {
                $table->dropForeign(['CountryId']);
                $table->dropColumn('CountryId');
            }
            if (Schema::hasColumn('t_HRHolidays', 'RegionScope')) {
                $table->dropColumn('RegionScope');
            }
        });
    }
};
