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
        Schema::table('t_TenderCommitteeEvaluations', function (Blueprint $table) {
            $table->foreign(['CommitteeID'])->references(['id'])->on('t_TenderCommittee')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CriteriaID'])->references(['Id'])->on('t_Criterias')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['MemberID'])->references(['id'])->on('t_TenderCommitteeMembers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SectionID'])->references(['Id'])->on('t_Sections')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TenderID'])->references(['Id'])->on('t_Tenders')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TenderCommitteeEvaluations', function (Blueprint $table) {
            $table->dropForeign('t_tendercommitteeevaluations_committeeid_foreign');
            $table->dropForeign('t_tendercommitteeevaluations_createdby_foreign');
            $table->dropForeign('t_tendercommitteeevaluations_criteriaid_foreign');
            $table->dropForeign('t_tendercommitteeevaluations_deletedby_foreign');
            $table->dropForeign('t_tendercommitteeevaluations_memberid_foreign');
            $table->dropForeign('t_tendercommitteeevaluations_modifiedby_foreign');
            $table->dropForeign('t_tendercommitteeevaluations_sectionid_foreign');
            $table->dropForeign('t_tendercommitteeevaluations_tenderid_foreign');
        });
    }
};
