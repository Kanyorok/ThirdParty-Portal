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
        Schema::table('t_ComplianceCalendarEntries', function (Blueprint $table) {
            $table->foreign(['ObligationID'])->references(['Id'])->on('t_ComplianceObligations')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ComplianceCalendarEntries', function (Blueprint $table) {
            $table->dropForeign('t_compliancecalendarentries_obligationid_foreign');
        });
    }
};
