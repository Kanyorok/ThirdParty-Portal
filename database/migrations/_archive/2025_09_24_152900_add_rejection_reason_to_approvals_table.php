<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('t_Approvals', 'RejectionReason')) {
            Schema::table('t_Approvals', function (Blueprint $table) {
                $table->string('RejectionReason', 500)->nullable()->after('Status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('t_Approvals', 'RejectionReason')) {
            Schema::table('t_Approvals', function (Blueprint $table) {
                $table->dropColumn('RejectionReason');
            });
        }
    }
};


