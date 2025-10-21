<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. t_MedicalFunds
        if (!Schema::hasTable('t_MedicalFunds')) {
            Schema::create('t_MedicalFunds', function (Blueprint $table) {
                $table->id('Id');
                $table->string('FundName', 255);
                $table->foreignId('ProviderId')->constrained('t_InsuranceProviders', 'Id');
                $table->foreignId('CoverageType')->constrained('t_CodeDetails', 'ID');
                $table->decimal('CoverageLimit', 18, 2)->nullable();
                $table->string('Description')->nullable();
                $table->boolean('IsActive')->default(true);
                $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn')->nullable();
                $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn')->nullable();
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn')->nullable();
            });
        }

        // 2. t_Coverages (lookup)
        if (!Schema::hasTable('t_Coverages')) {
            Schema::create('t_Coverages', function (Blueprint $table) {
                $table->id('Id');
                $table->string('Code')->unique();
                $table->string('Name');
                $table->string('Description')->nullable();
                $table->boolean('IsActive')->default(true);
                $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn')->nullable();
                $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn')->nullable();
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn')->nullable();
            });
        }

        // 3. t_MedicalFundContributors
        if (!Schema::hasTable('t_MedicalFundContributors')) {
            Schema::create('t_MedicalFundContributors', function (Blueprint $table) {
                $table->id('Id');
                $table->foreignId('FundId')->constrained('t_MedicalFunds', 'Id');
                $table->bigInteger('PartyId')->nullable();
                $table->string('ContributorNo', 50)->nullable();
                $table->foreignId('ThirdPartyId')->nullable()->constrained('t_ThirdParties', 'Id');
                $table->date('EffectiveFrom')->nullable();
                $table->date('EffectiveTo')->nullable();
                $table->foreignId('Status')->constrained('t_CodeDetails','Id');
                $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn')->nullable();
                $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn')->nullable();
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn')->nullable();
            });
        }

        // 4. t_MedicalFundBeneficiaries
        if (!Schema::hasTable('t_MedicalFundBeneficiaries')) {
            Schema::create('t_MedicalFundBeneficiaries', function (Blueprint $table) {
                $table->id('Id');
                $table->foreignId('FundId')->constrained('t_MedicalFunds', 'Id');
                $table->foreignId('ContributorId')->nullable()->constrained('t_MedicalFundContributors');
                $table->string('FullName', 255);
                $table->string('Relationship', 50)->nullable();
                $table->date('DateOfBirth')->nullable();
                $table->string('NationalID', 50)->nullable();
                $table->string('Contact', 50)->nullable();
                $table->boolean('IsActive')->default(true);
                $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn')->nullable();
                $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn')->nullable();
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn')->nullable();
            });
        }

        // 5. t_MedicalFundContributions
        if (!Schema::hasTable('t_MedicalFundContributions')) {
            Schema::create('t_MedicalFundContributions', function (Blueprint $table) {
                $table->id('Id');
                $table->foreignId('FundId')->constrained('t_MedicalFunds','Id');
                $table->foreignId('ContributorType')->nullable()->constrained('t_CodeDetails','ID');
                $table->foreignId('ContributorId')->nullable()->constrained('t_MedicalFundContributors','Id');
                $table->decimal('Amount', 18, 2);
                $table->date('ContributionDate');
                $table->string('Notes', 500)->nullable();
                $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn')->nullable();
                $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn')->nullable();
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn')->nullable();
            });
        }

        // 6. t_MedicalFundPackages
        if (!Schema::hasTable('t_MedicalFundPackages')) {
            Schema::create('t_MedicalFundPackages', function (Blueprint $table) {
                $table->id('Id');
                $table->foreignId('FundId')->constrained('t_MedicalFunds','Id');
                $table->string('Name', 100);
                $table->string('CoverageDescription', 255)->nullable();
                $table->decimal('Premium', 18, 2);
                $table->boolean('IsCompulsory')->default(false);
                $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn')->nullable();
                $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn')->nullable();
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn')->nullable();
            });
        }

        // 7. t_MedicalFundPackageCoverages (pivot)
        if (!Schema::hasTable('t_MedicalFundPackageCoverages')) {
            Schema::create('t_MedicalFundPackageCoverages', function (Blueprint $table) {
                $table->id('Id');
                $table->foreignId('PackageId')->constrained('t_MedicalFundPackages','Id');
                $table->foreignId('CoverageId')->constrained('t_Coverages', 'Id');
                $table->decimal('AnnualLimit', 18, 2)->nullable();
                $table->decimal('PerVisitLimit', 18, 2)->nullable();
                $table->integer('WaitingPeriodDays')->nullable();
                $table->string('Scope', 20)->nullable();
                $table->boolean('IsActive')->default(true);
                $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn')->nullable();
                $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn')->nullable();
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn')->nullable();
            });
        }

        // 8. t_MedicalFundContributorPackages (pivot)
        if (!Schema::hasTable('t_MedicalFundContributorPackages')) {
            Schema::create('t_MedicalFundContributorPackages', function (Blueprint $table) {
                $table->id('Id');
                $table->foreignId('ContributorId')->constrained('t_MedicalFundContributors','Id');
                $table->foreignId('PackageId')->constrained('t_MedicalFundPackages', 'Id');
                $table->boolean('IsActive')->default(true);
                $table->date('SubscribedOn')->useCurrent();
                $table->boolean('IsPrimary')->default(true);
                $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn')->nullable();
                $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn')->nullable();
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn')->nullable();
            });
        }

        // 9. t_MedicalFundDisbursements
        if (!Schema::hasTable('t_MedicalFundDisbursements')) {
            Schema::create('t_MedicalFundDisbursements', function (Blueprint $table) {
                $table->id('Id');
                $table->foreignId('FundId')->constrained('t_MedicalFunds','Id');
                $table->foreignId('ContributorId')->nullable()->constrained('t_MedicalFundContributors','Id');
                $table->foreignId('BeneficiaryId')->constrained('t_MedicalFundBeneficiaries','Id');
                $table->date('DisbursementDate');
                $table->decimal('Amount', 18, 2);
                $table->string('Purpose', 500)->nullable();
                $table->string('ApprovedBy')->nullable();
                $table->dateTime('ApprovedOn')->nullable();
                $table->foreignId('CoverageID')->nullable()->constrained('t_Coverages','Id');
                $table->foreignId('PackageID')->nullable()->constrained('t_MedicalFundPackages','Id');
                $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn')->nullable();
                $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn')->nullable();
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('t_MedicalFundDisbursements');
        Schema::dropIfExists('t_MedicalFundContributorPackages');
        Schema::dropIfExists('t_MedicalFundPackageCoverages');
        Schema::dropIfExists('t_MedicalFundPackages');
        Schema::dropIfExists('t_MedicalFundContributions');
        Schema::dropIfExists('t_MedicalFundBeneficiaries');
        Schema::dropIfExists('t_MedicalFundContributors');
        Schema::dropIfExists('t_Coverages');
        Schema::dropIfExists('t_MedicalFunds');
    }
};
