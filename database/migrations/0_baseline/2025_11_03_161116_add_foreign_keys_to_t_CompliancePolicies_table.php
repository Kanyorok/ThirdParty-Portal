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
        Schema::table('t_CompliancePolicies', function (Blueprint $table) {
            $table->foreign(['CategoryID'])->references(['Id'])->on('t_PolicyCategories')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ComplianceAreaID'])->references(['Id'])->on('t_ComplianceAreas')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_CompliancePolicies', function (Blueprint $table) {
            $table->dropForeign('t_compliancepolicies_categoryid_foreign');
            $table->dropForeign('t_compliancepolicies_complianceareaid_foreign');
        });
    }
};
