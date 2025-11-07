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
        Schema::table('t_PrequalificationRoundSections', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RoundId'])->references(['RoundID'])->on('t_PrequalificationRounds')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SectionId'])->references(['Id'])->on('t_Sections')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PrequalificationRoundSections', function (Blueprint $table) {
            $table->dropForeign('t_prequalificationroundsections_createdby_foreign');
            $table->dropForeign('t_prequalificationroundsections_deletedby_foreign');
            $table->dropForeign('t_prequalificationroundsections_modifiedby_foreign');
            $table->dropForeign('t_prequalificationroundsections_roundid_foreign');
            $table->dropForeign('t_prequalificationroundsections_sectionid_foreign');
        });
    }
};
