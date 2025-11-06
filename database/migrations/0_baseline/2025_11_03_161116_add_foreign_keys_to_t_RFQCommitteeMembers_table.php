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
        Schema::table('t_RFQCommitteeMembers', function (Blueprint $table) {
            $table->foreign(['CommitteeID'])->references(['id'])->on('t_RFQCommittee')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RFQID'])->references(['Id'])->on('t_RFQ')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UserID'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQCommitteeMembers', function (Blueprint $table) {
            $table->dropForeign('t_rfqcommitteemembers_committeeid_foreign');
            $table->dropForeign('t_rfqcommitteemembers_createdby_foreign');
            $table->dropForeign('t_rfqcommitteemembers_deletedby_foreign');
            $table->dropForeign('t_rfqcommitteemembers_modifiedby_foreign');
            $table->dropForeign('t_rfqcommitteemembers_rfqid_foreign');
            $table->dropForeign('t_rfqcommitteemembers_userid_foreign');
        });
    }
};
