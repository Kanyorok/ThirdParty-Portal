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
        Schema::table('t_ComplianceFilingTemplates', function (Blueprint $table) {
            $table->foreign(['FilingTypeID'])->references(['Id'])->on('t_FilingTypes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FormatID'])->references(['Id'])->on('t_FileFormats')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RegulatorID'])->references(['Id'])->on('t_RegulatoryBodies')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ComplianceFilingTemplates', function (Blueprint $table) {
            $table->dropForeign('t_compliancefilingtemplates_filingtypeid_foreign');
            $table->dropForeign('t_compliancefilingtemplates_formatid_foreign');
            $table->dropForeign('t_compliancefilingtemplates_regulatorid_foreign');
        });
    }
};
