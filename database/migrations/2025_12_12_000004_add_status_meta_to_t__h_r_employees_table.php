<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HREmployees', 'StatusReason')) {
                $table->string('StatusReason', 255)->nullable()->after('Status');
            }
            if (!Schema::hasColumn('t_HREmployees', 'StatusChangedOn')) {
                $table->dateTime('StatusChangedOn')->nullable()->after('StatusReason');
            }
            if (!Schema::hasColumn('t_HREmployees', 'StatusChangedBy')) {
                $table->unsignedBigInteger('StatusChangedBy')->nullable()->after('StatusChangedOn');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (Schema::hasColumn('t_HREmployees', 'StatusReason')) {
                $table->dropColumn('StatusReason');
            }
            if (Schema::hasColumn('t_HREmployees', 'StatusChangedOn')) {
                $table->dropColumn('StatusChangedOn');
            }
            if (Schema::hasColumn('t_HREmployees', 'StatusChangedBy')) {
                $table->dropColumn('StatusChangedBy');
            }
        });
    }
};
