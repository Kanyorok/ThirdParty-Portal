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
        Schema::table('t_BancassuranceClaimClosures', function (Blueprint $table) {
            $table->foreign(['ClaimId'])->references(['Id'])->on('t_BancassuranceClaims ')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceClaimClosures', function (Blueprint $table) {
            $table->dropForeign('t_bancassuranceclaimclosures_claimid_foreign');
            $table->dropForeign('t_bancassuranceclaimclosures_createdby_foreign');
            $table->dropForeign('t_bancassuranceclaimclosures_deletedby_foreign');
            $table->dropForeign('t_bancassuranceclaimclosures_modifiedby_foreign');
        });
    }
};
