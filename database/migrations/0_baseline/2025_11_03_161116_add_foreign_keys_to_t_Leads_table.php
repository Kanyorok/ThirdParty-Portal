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
        Schema::table('t_Leads', function (Blueprint $table) {
            $table->foreign(['ArchivedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CountryId'])->references(['Id'])->on('t_Countries')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CustomerType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ImageId'])->references(['ImageID'])->on('t_Images')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Industry'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LeadLossReason'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LocationID'])->references(['ID'])->on('t_Localities')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Source'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Leads', function (Blueprint $table) {
            $table->dropForeign('t_leads_archivedby_foreign');
            $table->dropForeign('t_leads_countryid_foreign');
            $table->dropForeign('t_leads_createdby_foreign');
            $table->dropForeign('t_leads_customertype_foreign');
            $table->dropForeign('t_leads_deletedby_foreign');
            $table->dropForeign('t_leads_imageid_foreign');
            $table->dropForeign('t_leads_industry_foreign');
            $table->dropForeign('t_leads_leadlossreason_foreign');
            $table->dropForeign('t_leads_locationid_foreign');
            $table->dropForeign('t_leads_modifiedby_foreign');
            $table->dropForeign('t_leads_source_foreign');
        });
    }
};
