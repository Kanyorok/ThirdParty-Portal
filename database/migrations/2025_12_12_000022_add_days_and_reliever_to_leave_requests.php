<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRLeaveRequests', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRLeaveRequests', 'RelieverID')) {
                $table->unsignedBigInteger('RelieverID')->nullable()->after('LeaveTypeID');
            }
            // TotalDays already exists; ensure it's present for display
        });
    }

    public function down(): void
    {
        Schema::table('t_HRLeaveRequests', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRLeaveRequests', 'RelieverID')) {
                $table->dropColumn('RelieverID');
            }
        });
    }
};
