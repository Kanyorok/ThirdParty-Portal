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
        Schema::table('t_ComplianceObligations', function (Blueprint $table) {
            $table->foreign(['ComplianceAreaID'])->references(['Id'])->on('t_ComplianceAreas')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RegulatorID'])->references(['Id'])->on('t_RegulatoryBodies')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ComplianceObligations', function (Blueprint $table) {
            $table->dropForeign('t_complianceobligations_complianceareaid_foreign');
            $table->dropForeign('t_complianceobligations_regulatorid_foreign');
        });
    }
};
