<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HREmployees', 'Religion')) {
                $table->string('Religion', 100)->nullable()->after('Gender');
            }
        });

        Schema::table('t_HRHolidays', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRHolidays', 'AppliesToReligion')) {
                $table->string('AppliesToReligion', 100)->nullable()->after('Region');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRHolidays', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRHolidays', 'AppliesToReligion')) {
                $table->dropColumn('AppliesToReligion');
            }
        });

        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (Schema::hasColumn('t_HREmployees', 'Religion')) {
                $table->dropColumn('Religion');
            }
        });
    }
};
