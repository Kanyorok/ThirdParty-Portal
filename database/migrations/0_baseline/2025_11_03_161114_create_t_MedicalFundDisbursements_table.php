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
        Schema::create('t_MedicalFundDisbursements', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('FundId');
            $table->bigInteger('ContributorId')->nullable();
            $table->bigInteger('BeneficiaryId');
            $table->date('DisbursementDate');
            $table->decimal('Amount', 18);
            $table->string('Purpose', 500)->nullable();
            $table->string('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->bigInteger('CoverageID')->nullable();
            $table->bigInteger('PackageID')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_medica__3214ec076d062f0b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MedicalFundDisbursements');
    }
};
