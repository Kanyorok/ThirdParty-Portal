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
        Schema::table('t_MedicalFundPackageCoverages', function (Blueprint $table) {
            $table->foreign(['CoverageId'])->references(['Id'])->on('t_Coverages')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PackageId'])->references(['Id'])->on('t_MedicalFundPackages')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MedicalFundPackageCoverages', function (Blueprint $table) {
            $table->dropForeign('t_medicalfundpackagecoverages_coverageid_foreign');
            $table->dropForeign('t_medicalfundpackagecoverages_createdby_foreign');
            $table->dropForeign('t_medicalfundpackagecoverages_deletedby_foreign');
            $table->dropForeign('t_medicalfundpackagecoverages_modifiedby_foreign');
            $table->dropForeign('t_medicalfundpackagecoverages_packageid_foreign');
        });
    }
};
