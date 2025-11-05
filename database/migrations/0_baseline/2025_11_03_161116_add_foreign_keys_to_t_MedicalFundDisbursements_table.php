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
        Schema::table('t_MedicalFundDisbursements', function (Blueprint $table) {
            $table->foreign(['BeneficiaryId'])->references(['Id'])->on('t_MedicalFundBeneficiaries')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ContributorId'])->references(['Id'])->on('t_MedicalFundContributors')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CoverageID'])->references(['Id'])->on('t_Coverages')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FundId'])->references(['Id'])->on('t_MedicalFunds')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PackageID'])->references(['Id'])->on('t_MedicalFundPackages')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MedicalFundDisbursements', function (Blueprint $table) {
            $table->dropForeign('t_medicalfunddisbursements_beneficiaryid_foreign');
            $table->dropForeign('t_medicalfunddisbursements_contributorid_foreign');
            $table->dropForeign('t_medicalfunddisbursements_coverageid_foreign');
            $table->dropForeign('t_medicalfunddisbursements_createdby_foreign');
            $table->dropForeign('t_medicalfunddisbursements_deletedby_foreign');
            $table->dropForeign('t_medicalfunddisbursements_fundid_foreign');
            $table->dropForeign('t_medicalfunddisbursements_modifiedby_foreign');
            $table->dropForeign('t_medicalfunddisbursements_packageid_foreign');
        });
    }
};
