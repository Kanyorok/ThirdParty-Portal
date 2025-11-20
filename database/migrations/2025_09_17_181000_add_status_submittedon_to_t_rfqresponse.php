<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_RFQResponse', function (Blueprint $table) {
            if (!Schema::hasColumn('t_RFQResponse', 'Status')) {
                $table->string('Status', 16)->default('DRAFT')->after('DurationDays');
            }
            if (!Schema::hasColumn('t_RFQResponse', 'SubmittedOn')) {
                $table->dateTime('SubmittedOn')->nullable()->after('Status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_RFQResponse', function (Blueprint $table) {
            if (Schema::hasColumn('t_RFQResponse', 'SubmittedOn')) {
                $table->dropColumn('SubmittedOn');
            }
            if (Schema::hasColumn('t_RFQResponse', 'Status')) {
                $table->dropColumn('Status');
            }
        });
    }
};


