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
        Schema::table('t_BancassuranceClaimAssessments', function (Blueprint $table) {
            $table->foreign(['AssessedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ClaimId'])->references(['Id'])->on('t_BancassuranceClaims ')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Decision'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceClaimAssessments', function (Blueprint $table) {
            $table->dropForeign('t_bancassuranceclaimassessments_assessedby_foreign');
            $table->dropForeign('t_bancassuranceclaimassessments_claimid_foreign');
            $table->dropForeign('t_bancassuranceclaimassessments_createdby_foreign');
            $table->dropForeign('t_bancassuranceclaimassessments_decision_foreign');
            $table->dropForeign('t_bancassuranceclaimassessments_deletedby_foreign');
            $table->dropForeign('t_bancassuranceclaimassessments_modifiedby_foreign');
        });
    }
};
