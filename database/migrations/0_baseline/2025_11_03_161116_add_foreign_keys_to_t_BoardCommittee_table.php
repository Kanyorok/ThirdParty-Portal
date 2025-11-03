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
        Schema::table('t_BoardCommittee', function (Blueprint $table) {
            $table->foreign(['BoardId'])->references(['Id'])->on('t_BoardMembers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CommitteeId'])->references(['Id'])->on('t_Committees')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BoardCommittee', function (Blueprint $table) {
            $table->dropForeign('t_boardcommittee_boardid_foreign');
            $table->dropForeign('t_boardcommittee_committeeid_foreign');
            $table->dropForeign('t_boardcommittee_createdby_foreign');
            $table->dropForeign('t_boardcommittee_modifiedby_foreign');
        });
    }
};
