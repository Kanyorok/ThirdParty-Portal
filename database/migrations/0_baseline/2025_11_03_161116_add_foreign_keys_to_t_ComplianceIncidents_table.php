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
        Schema::table('t_ComplianceIncidents', function (Blueprint $table) {
            $table->foreign(['ObligationID'])->references(['Id'])->on('t_ComplianceObligations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['SeverityID'])->references(['Id'])->on('t_IncidentSeverityLevels')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ComplianceIncidents', function (Blueprint $table) {
            $table->dropForeign('t_complianceincidents_obligationid_foreign');
            $table->dropForeign('t_complianceincidents_severityid_foreign');
        });
    }
};
