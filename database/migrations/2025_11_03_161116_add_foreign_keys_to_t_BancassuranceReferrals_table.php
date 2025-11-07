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
        Schema::table('t_BancassuranceReferrals', function (Blueprint $table) {
            $table->foreign(['BranchId'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InsuranceProductId'])->references(['Id'])->on('t_InsuranceProducts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PreferredInsurerId'])->references(['Id'])->on('t_InsuranceProviders')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ReferredBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceReferrals', function (Blueprint $table) {
            $table->dropForeign('t_bancassurancereferrals_branchid_foreign');
            $table->dropForeign('t_bancassurancereferrals_createdby_foreign');
            $table->dropForeign('t_bancassurancereferrals_deletedby_foreign');
            $table->dropForeign('t_bancassurancereferrals_insuranceproductid_foreign');
            $table->dropForeign('t_bancassurancereferrals_modifiedby_foreign');
            $table->dropForeign('t_bancassurancereferrals_preferredinsurerid_foreign');
            $table->dropForeign('t_bancassurancereferrals_referredby_foreign');
        });
    }
};
