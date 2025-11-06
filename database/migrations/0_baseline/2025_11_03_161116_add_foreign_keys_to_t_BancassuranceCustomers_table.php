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
        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Gender'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['MaritalStatus'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ReferralID'])->references(['Id'])->on('t_BancassuranceReferrals')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ThirdPartyId'])->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            $table->dropForeign('t_bancassurancecustomers_createdby_foreign');
            $table->dropForeign('t_bancassurancecustomers_deletedby_foreign');
            $table->dropForeign('t_bancassurancecustomers_gender_foreign');
            $table->dropForeign('t_bancassurancecustomers_maritalstatus_foreign');
            $table->dropForeign('t_bancassurancecustomers_modifiedby_foreign');
            $table->dropForeign('t_bancassurancecustomers_referralid_foreign');
            $table->dropForeign('t_bancassurancecustomers_thirdpartyid_foreign');
        });
    }
};
