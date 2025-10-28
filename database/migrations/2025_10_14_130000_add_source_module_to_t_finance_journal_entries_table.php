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
            $table->string('SourceModule')->default('1100000')->after('Type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceJournalEntries', function (Blueprint $table) {
            $table->dropColumn('SourceModule');
        });
    }
};
