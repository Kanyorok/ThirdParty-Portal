<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_FinanceJournalEntries', function (Blueprint $table) {
            if (!Schema::hasColumn('t_FinanceJournalEntries', 'IsReversed')) {
                $table->boolean('IsReversed')->default(false)->after('Status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceJournalEntries', function (Blueprint $table) {
            if (Schema::hasColumn('t_FinanceJournalEntries', 'IsReversed')) {
                $table->dropColumn('IsReversed');
            }
        });
    }
};


