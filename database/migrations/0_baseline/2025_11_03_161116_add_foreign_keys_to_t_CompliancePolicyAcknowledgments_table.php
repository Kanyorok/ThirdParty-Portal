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
        Schema::table('t_CompliancePolicyAcknowledgments', function (Blueprint $table) {
            $table->foreign(['PolicyID'])->references(['Id'])->on('t_CompliancePolicies')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_CompliancePolicyAcknowledgments', function (Blueprint $table) {
            $table->dropForeign('t_compliancepolicyacknowledgments_policyid_foreign');
        });
    }
};
