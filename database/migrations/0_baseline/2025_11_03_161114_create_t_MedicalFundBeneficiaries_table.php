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
        Schema::create('t_MedicalFundBeneficiaries', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('FundId');
            $table->bigInteger('ContributorId')->nullable();
            $table->string('FullName');
            $table->string('Relationship', 50)->nullable();
            $table->date('DateOfBirth')->nullable();
            $table->string('NationalID', 50)->nullable();
            $table->string('Contact', 50)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_medica__3214ec076781c17d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MedicalFundBeneficiaries');
    }
};
