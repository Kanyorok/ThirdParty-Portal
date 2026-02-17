<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRPayrollRuns', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollRuns', 'FinanceJournalEntryID')) {
                $table->unsignedBigInteger('FinanceJournalEntryID')->nullable()->after('Status');
            }
            if (!Schema::hasColumn('t_HRPayrollRuns', 'FinancePostingMode')) {
                $table->string('FinancePostingMode', 50)->nullable()->after('FinanceJournalEntryID');
            }
            if (!Schema::hasColumn('t_HRPayrollRuns', 'FinancePostedOn')) {
                $table->dateTime('FinancePostedOn')->nullable()->after('FinancePostingMode');
            }
            if (!Schema::hasColumn('t_HRPayrollRuns', 'FinancePostedBy')) {
                $table->unsignedBigInteger('FinancePostedBy')->nullable()->after('FinancePostedOn');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollRuns', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollRuns', 'FinancePostedBy')) {
                $table->dropColumn('FinancePostedBy');
            }
            if (Schema::hasColumn('t_HRPayrollRuns', 'FinancePostedOn')) {
                $table->dropColumn('FinancePostedOn');
            }
            if (Schema::hasColumn('t_HRPayrollRuns', 'FinancePostingMode')) {
                $table->dropColumn('FinancePostingMode');
            }
            if (Schema::hasColumn('t_HRPayrollRuns', 'FinanceJournalEntryID')) {
                $table->dropColumn('FinanceJournalEntryID');
            }
        });
    }
};

