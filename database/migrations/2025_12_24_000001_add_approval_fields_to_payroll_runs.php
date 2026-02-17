<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRPayrollRuns', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollRuns', 'ApprovedBy')) {
                $table->unsignedBigInteger('ApprovedBy')->nullable();
            }
            if (!Schema::hasColumn('t_HRPayrollRuns', 'ApprovedOn')) {
                $table->dateTime('ApprovedOn')->nullable();
            }
            if (!Schema::hasColumn('t_HRPayrollRuns', 'RejectedBy')) {
                $table->unsignedBigInteger('RejectedBy')->nullable();
            }
            if (!Schema::hasColumn('t_HRPayrollRuns', 'RejectedOn')) {
                $table->dateTime('RejectedOn')->nullable();
            }
            if (!Schema::hasColumn('t_HRPayrollRuns', 'RejectionReason')) {
                $table->string('RejectionReason', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollRuns', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollRuns', 'RejectionReason')) {
                $table->dropColumn('RejectionReason');
            }
            if (Schema::hasColumn('t_HRPayrollRuns', 'RejectedOn')) {
                $table->dropColumn('RejectedOn');
            }
            if (Schema::hasColumn('t_HRPayrollRuns', 'RejectedBy')) {
                $table->dropColumn('RejectedBy');
            }
            if (Schema::hasColumn('t_HRPayrollRuns', 'ApprovedOn')) {
                $table->dropColumn('ApprovedOn');
            }
            if (Schema::hasColumn('t_HRPayrollRuns', 'ApprovedBy')) {
                $table->dropColumn('ApprovedBy');
            }
        });
    }
};
