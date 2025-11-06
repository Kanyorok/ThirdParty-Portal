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
        Schema::create('t_MedicalFundContributors', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('FundId');
            $table->bigInteger('PartyId')->nullable();
            $table->string('ContributorNo', 50)->nullable();
            $table->bigInteger('ThirdPartyId')->nullable();
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->bigInteger('Status');
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_medica__3214ec07d1460ba3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MedicalFundContributors');
    }
};
