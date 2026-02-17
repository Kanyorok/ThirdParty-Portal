<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRLeaveTypes', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRLeaveTypes', 'AllowedGender')) {
                $table->string('AllowedGender', 10)->nullable()->after('IsAccruing'); // Male, Female, null = any
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRLeaveTypes', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRLeaveTypes', 'AllowedGender')) {
                $table->dropColumn('AllowedGender');
            }
        });
    }
};
