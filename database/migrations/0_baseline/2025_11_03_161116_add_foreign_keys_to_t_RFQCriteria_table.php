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
        Schema::table('t_RFQCriteria', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CriteriaID'])->references(['Id'])->on('t_Criterias')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RFQID'])->references(['Id'])->on('t_RFQ')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['SectionID'])->references(['Id'])->on('t_Sections')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQCriteria', function (Blueprint $table) {
            $table->dropForeign('t_rfqcriteria_createdby_foreign');
            $table->dropForeign('t_rfqcriteria_criteriaid_foreign');
            $table->dropForeign('t_rfqcriteria_deletedby_foreign');
            $table->dropForeign('t_rfqcriteria_modifiedby_foreign');
            $table->dropForeign('t_rfqcriteria_rfqid_foreign');
            $table->dropForeign('t_rfqcriteria_sectionid_foreign');
        });
    }
};
