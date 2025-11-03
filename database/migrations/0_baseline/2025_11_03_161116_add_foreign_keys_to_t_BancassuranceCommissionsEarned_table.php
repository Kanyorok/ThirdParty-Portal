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
        Schema::table('t_BancassuranceCommissionsEarned', function (Blueprint $table) {
            $table->foreign(['CommissionRuleId'])->references(['Id'])->on('t_BancassuranceCommissionRules')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['EarnedByType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PolicyId'])->references(['Id'])->on('t_BancassurancePolicies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ReferralId'])->references(['Id'])->on('t_BancassuranceReferrals')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceCommissionsEarned', function (Blueprint $table) {
            $table->dropForeign('t_bancassurancecommissionsearned_commissionruleid_foreign');
            $table->dropForeign('t_bancassurancecommissionsearned_createdby_foreign');
            $table->dropForeign('t_bancassurancecommissionsearned_deletedby_foreign');
            $table->dropForeign('t_bancassurancecommissionsearned_earnedbytype_foreign');
            $table->dropForeign('t_bancassurancecommissionsearned_modifiedby_foreign');
            $table->dropForeign('t_bancassurancecommissionsearned_policyid_foreign');
            $table->dropForeign('t_bancassurancecommissionsearned_referralid_foreign');
        });
    }
};
