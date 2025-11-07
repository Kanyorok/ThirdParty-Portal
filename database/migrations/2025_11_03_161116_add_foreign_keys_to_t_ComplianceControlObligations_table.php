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
        Schema::table('t_ComplianceControlObligations', function (Blueprint $table) {
            $table->foreign(['ControlID'])->references(['Id'])->on('t_ComplianceControls')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ObligationID'])->references(['Id'])->on('t_ComplianceObligations')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ComplianceControlObligations', function (Blueprint $table) {
            $table->dropForeign('t_compliancecontrolobligations_controlid_foreign');
            $table->dropForeign('t_compliancecontrolobligations_obligationid_foreign');
        });
    }
};
