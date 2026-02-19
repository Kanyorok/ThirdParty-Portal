<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRLeaveTypes', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRLeaveTypes', 'IsAccruing')) {
                $table->boolean('IsAccruing')->default(false)->after('AllowCarryForward');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRLeaveTypes', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRLeaveTypes', 'IsAccruing')) {
                $table->dropColumn('IsAccruing');
            }
        });
    }
};
