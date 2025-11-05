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
        Schema::create('t_BancassuranceReferrals', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ClientName');
            $table->string('ClientIDNumber');
            $table->string('ClientPhone');
            $table->string('ClientEmail');
            $table->bigInteger('BranchId');
            $table->bigInteger('ReferredBy');
            $table->date('ReferralDate')->nullable();
            $table->bigInteger('InsuranceProductId');
            $table->bigInteger('PreferredInsurerId');
            $table->string('Remarks')->nullable();
            $table->string('Status');
            $table->string('AssignedTo')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec07f4685e3a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceReferrals');
    }
};
