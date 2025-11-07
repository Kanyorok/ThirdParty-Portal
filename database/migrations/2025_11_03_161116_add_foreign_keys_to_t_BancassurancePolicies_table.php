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
        Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CustomerID'])->references(['Id'])->on('t_BancassuranceCustomers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InsurerID'])->references(['Id'])->on('t_InsuranceProviders')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PaymentFrequency'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ProductID'])->references(['Id'])->on('t_InsuranceProducts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ReferralID'])->references(['Id'])->on('t_BancassuranceReferrals')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RiderAddOnId'])->references(['Id'])->on('t_InsuranceProductRiders')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassurancePolicies', function (Blueprint $table) {
            $table->dropForeign('t_bancassurancepolicies_createdby_foreign');
            $table->dropForeign('t_bancassurancepolicies_customerid_foreign');
            $table->dropForeign('t_bancassurancepolicies_deletedby_foreign');
            $table->dropForeign('t_bancassurancepolicies_insurerid_foreign');
            $table->dropForeign('t_bancassurancepolicies_modifiedby_foreign');
            $table->dropForeign('t_bancassurancepolicies_paymentfrequency_foreign');
            $table->dropForeign('t_bancassurancepolicies_productid_foreign');
            $table->dropForeign('t_bancassurancepolicies_referralid_foreign');
            $table->dropForeign('t_bancassurancepolicies_rideraddonid_foreign');
        });
    }
};
