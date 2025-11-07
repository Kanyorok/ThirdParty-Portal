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
        Schema::table('t_EngagedAuditors', function (Blueprint $table) {
            $table->foreign(['AuditorId'])->references(['Id'])->on('t_Auditors')->onUpdate('no action')->onDelete('cascade');
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
        Schema::table('t_EngagedAuditors', function (Blueprint $table) {
            $table->dropForeign('t_engagedauditors_auditorid_foreign');
            $table->dropForeign('t_engagedauditors_createdby_foreign');
            $table->dropForeign('t_engagedauditors_deletedby_foreign');
            $table->dropForeign('t_engagedauditors_modifiedby_foreign');
        });
    }
};
