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
        Schema::table('t_MedicalFundBeneficiaries', function (Blueprint $table) {
            $table->foreign(['ContributorId'])->references(['Id'])->on('t_MedicalFundContributors')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FundId'])->references(['Id'])->on('t_MedicalFunds')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MedicalFundBeneficiaries', function (Blueprint $table) {
            $table->dropForeign('t_medicalfundbeneficiaries_contributorid_foreign');
            $table->dropForeign('t_medicalfundbeneficiaries_createdby_foreign');
            $table->dropForeign('t_medicalfundbeneficiaries_deletedby_foreign');
            $table->dropForeign('t_medicalfundbeneficiaries_fundid_foreign');
            $table->dropForeign('t_medicalfundbeneficiaries_modifiedby_foreign');
        });
    }
};
