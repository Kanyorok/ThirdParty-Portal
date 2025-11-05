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
        Schema::table('t_TenderCommitteeMembers', function (Blueprint $table) {
            $table->foreign(['CommitteeID'])->references(['id'])->on('t_TenderCommittee')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TenderID'])->references(['Id'])->on('t_Tenders')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UserID'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TenderCommitteeMembers', function (Blueprint $table) {
            $table->dropForeign('t_tendercommitteemembers_committeeid_foreign');
            $table->dropForeign('t_tendercommitteemembers_createdby_foreign');
            $table->dropForeign('t_tendercommitteemembers_deletedby_foreign');
            $table->dropForeign('t_tendercommitteemembers_modifiedby_foreign');
            $table->dropForeign('t_tendercommitteemembers_tenderid_foreign');
            $table->dropForeign('t_tendercommitteemembers_userid_foreign');
        });
    }
};
