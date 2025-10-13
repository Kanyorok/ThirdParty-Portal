<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // t_MedicalFunds
        if (!Schema::hasTable('t_MedicalFunds')) {
            Schema::create('t_MedicalFunds', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('FundName', 255);
                $table->bigInteger('ProviderID'); // FK -> t_InsuranceProviders (not enforced here)
                $table->string('CoverageType', 100)->nullable();
                $table->decimal('CoverageLimit', 18, 2)->nullable();
                $table->text('Description')->nullable();
                $table->boolean('IsActive')->default(1);
                $table->bigInteger('CreatedBy')->nullable();
                $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
                $table->bigInteger('ModifiedBy')->nullable();
                $table->dateTime('ModifiedOn')->nullable();
                $table->bigInteger('DeletedBy')->nullable();
                $table->dateTime('DeletedOn')->nullable();
            });
        }

        // t_MedicalFundContributors
        if (!Schema::hasTable('t_MedicalFundContributors')) {
            Schema::create('t_MedicalFundContributors', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->bigInteger('FundID'); // FK -> t_MedicalFunds.ID
                $table->bigInteger('PartyID')->nullable();
                $table->string('ContributorNo', 50)->nullable();
                $table->string('FullName', 255);
                $table->string('Email', 150)->nullable();
                $table->string('Phone', 50)->nullable();
                $table->date('EffectiveFrom')->nullable();
                $table->date('EffectiveTo')->nullable();
                $table->string('Status', 30)->default('Active'); // Active, Suspended, Closed
                $table->bigInteger('CreatedBy')->nullable();
                $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
                $table->bigInteger('ModifiedBy')->nullable();
                $table->dateTime('ModifiedOn')->nullable();
                $table->bigInteger('DeletedBy')->nullable();
                $table->dateTime('DeletedOn')->nullable();

                // Helpful indexes
                $table->index('FundID');
                $table->index('PartyID');
            });
        }

        // t_MedicalFundBeneficiaries
        if (!Schema::hasTable('t_MedicalFundBeneficiaries')) {
            Schema::create('t_MedicalFundBeneficiaries', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->bigInteger('FundID'); // FK -> t_MedicalFunds.ID
                $table->bigInteger('ContributorID')->nullable(); // FK -> t_MedicalFundContributors.ID
                $table->string('FullName', 255);
                $table->string('Relationship', 50)->nullable();
                $table->date('DateOfBirth')->nullable();
                $table->string('NationalID', 50)->nullable();
                $table->string('Contact', 50)->nullable();
                $table->boolean('IsActive')->default(1);
                $table->bigInteger('CreatedBy')->nullable();
                $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
                $table->bigInteger('ModifiedBy')->nullable();
                $table->dateTime('ModifiedOn')->nullable();
                $table->bigInteger('DeletedBy')->nullable();
                $table->dateTime('DeletedOn')->nullable();

                $table->index('FundID');
                $table->index('ContributorID'); // IX_MedFundBeneficiaries_Contributor (named below via raw if needed)
            });
        }

        // t_MedicalFundContributions
        if (!Schema::hasTable('t_MedicalFundContributions')) {
            Schema::create('t_MedicalFundContributions', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->bigInteger('FundID'); // FK -> t_MedicalFunds.ID
                $table->string('ContributorType', 50)->nullable();
                $table->bigInteger('ContributorID')->nullable(); // FK -> t_MedicalFundContributors.ID
                $table->decimal('Amount', 18, 2);
                $table->date('ContributionDate');
                $table->string('Notes', 500)->nullable();
                $table->bigInteger('CreatedBy')->nullable();
                $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
                $table->bigInteger('ModifiedBy')->nullable();
                $table->dateTime('ModifiedOn')->nullable();
                $table->bigInteger('DeletedBy')->nullable();
                $table->dateTime('DeletedOn')->nullable();

                $table->index('FundID');
                $table->index(['ContributorID', 'ContributionDate']); // IX_MedFundContribs_Contributor
            });
        }

        // t_MedicalFundDisbursements
        if (!Schema::hasTable('t_MedicalFundDisbursements')) {
            Schema::create('t_MedicalFundDisbursements', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->bigInteger('FundID'); // FK -> t_MedicalFunds.ID
                $table->bigInteger('ContributorID')->nullable(); // FK -> t_MedicalFundContributors.ID
                $table->bigInteger('BeneficiaryID'); // FK -> t_MedicalFundBeneficiaries.ID
                $table->date('DisbursementDate');
                $table->decimal('Amount', 18, 2);
                $table->string('Purpose', 500)->nullable();
                $table->bigInteger('ApprovedBy')->nullable();
                $table->dateTime('ApprovedOn')->nullable();
                $table->bigInteger('CoverageID')->nullable(); // FK -> t_Coverages.ID
                $table->bigInteger('PackageID')->nullable();     // FK -> t_MedicalFundPackages.ID
                $table->bigInteger('CreatedBy')->nullable();
                $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
                $table->bigInteger('ModifiedBy')->nullable();
                $table->dateTime('ModifiedOn')->nullable();
                $table->bigInteger('DeletedBy')->nullable();
                $table->dateTime('DeletedOn')->nullable();

                $table->index('FundID');
                $table->index('ContributorID'); // IX_MFD_Contributor
                $table->index('CoverageID');    // IX_MFD_Coverage
                $table->index('PackageID');     // IX_MFD_Package
            });
        }

        // t_MedicalFundPackages
        if (!Schema::hasTable('t_MedicalFundPackages')) {
            Schema::create('t_MedicalFundPackages', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->bigInteger('FundID'); // FK -> t_MedicalFunds.ID
                $table->string('Name', 100);
                $table->string('CoverageDescription', 255)->nullable();
                $table->decimal('Premium', 18, 2);
                $table->boolean('IsCompulsory')->default(0);
                $table->bigInteger('CreatedBy')->nullable();
                $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
                $table->bigInteger('ModifiedBy')->nullable();
                $table->dateTime('ModifiedOn')->nullable();
                $table->bigInteger('DeletedBy')->nullable();
                $table->dateTime('DeletedOn')->nullable();

                $table->index('FundID');
            });
        }

        // t_MedicalFundContributorPackages (pivot)
        if (!Schema::hasTable('t_MedicalFundContributorPackages')) {
            Schema::create('t_MedicalFundContributorPackages', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->bigInteger('ContributorID'); // FK -> t_MedicalFundContributors.ID
                $table->bigInteger('PackageID');     // FK -> t_MedicalFundPackages.ID
                $table->boolean('IsActive')->default(1);
                $table->date('SubscribedOn')->default(DB::raw('GETDATE()'));
                $table->boolean('IsPrimary')->default(1); // single primary per contributor enforced via filtered index
                $table->bigInteger('CreatedBy')->nullable();
                $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));

                $table->index('ContributorID');
                $table->index('PackageID');
            });
        }

        // t_Coverages (lookup)
        if (!Schema::hasTable('t_Coverages')) {
            Schema::create('t_Coverages', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Code', 50)->unique();
                $table->string('Name', 150);
                $table->string('Description', 500)->nullable();
                $table->boolean('IsActive')->default(1);
                $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
                $table->bigInteger('CreatedBy')->nullable();
                $table->dateTime('ModifiedOn')->nullable();
                $table->bigInteger('ModifiedBy')->nullable();
            });
        }

        // t_MedicalFundPackageCoverages (pivot)
        if (!Schema::hasTable('t_MedicalFundPackageCoverages')) {
            Schema::create('t_MedicalFundPackageCoverages', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->bigInteger('PackageID'); // FK -> t_MedicalFundPackages.ID
                $table->bigInteger('CoverageID'); // FK -> t_Coverages.ID
                $table->decimal('AnnualLimit', 18, 2)->nullable();
                $table->decimal('PerVisitLimit', 18, 2)->nullable();
                $table->integer('WaitingPeriodDays')->nullable();
                $table->string('Scope', 20)->nullable(); // PerBeneficiary | PerFamily
                $table->boolean('IsActive')->default(1);
                $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
                $table->bigInteger('CreatedBy')->nullable();
                $table->dateTime('ModifiedOn')->nullable();
                $table->bigInteger('ModifiedBy')->nullable();

                $table->index('PackageID');
                $table->index('CoverageID');
            });
        }

        // t_BeneficiaryRelationships (lookup)
        if (!Schema::hasTable('t_BeneficiaryRelationships')) {
            Schema::create('t_BeneficiaryRelationships', function (Blueprint $table) {
                $table->bigIncrements('ID');
                $table->string('Code', 50)->unique();
                $table->string('Name', 100);
                $table->boolean('IsActive')->default(1);
            });
        }

        // Alter existing tables to add new columns where needed
        if (Schema::hasTable('t_MedicalFundBeneficiaries') && !Schema::hasColumn('t_MedicalFundBeneficiaries', 'ContributorID')) {
            Schema::table('t_MedicalFundBeneficiaries', function (Blueprint $table) {
                $table->bigInteger('ContributorID')->nullable()->after('FundID');
                $table->index('ContributorID');
            });
        }

        if (Schema::hasTable('t_MedicalFundContributions') && !Schema::hasColumn('t_MedicalFundContributions', 'ContributorID')) {
            Schema::table('t_MedicalFundContributions', function (Blueprint $table) {
                $table->bigInteger('ContributorID')->nullable()->after('ContributorType');
                $table->index(['ContributorID', 'ContributionDate']);
            });
        }

        if (Schema::hasTable('t_MedicalFundDisbursements')) {
            Schema::table('t_MedicalFundDisbursements', function (Blueprint $table) {
                if (!Schema::hasColumn('t_MedicalFundDisbursements', 'ContributorID')) {
                    $table->bigInteger('ContributorID')->nullable()->after('FundID');
                    $table->index('ContributorID');
                }
                if (!Schema::hasColumn('t_MedicalFundDisbursements', 'CoverageID')) {
                    $table->bigInteger('CoverageID')->nullable()->after('ApprovedOn');
                    $table->index('CoverageID');
                }
                if (!Schema::hasColumn('t_MedicalFundDisbursements', 'PackageID')) {
                    $table->bigInteger('PackageID')->nullable()->after('CoverageID');
                    $table->index('PackageID');
                }
            });
        }

        if (Schema::hasTable('t_MedicalFundContributorPackages') && !Schema::hasColumn('t_MedicalFundContributorPackages', 'IsPrimary')) {
            Schema::table('t_MedicalFundContributorPackages', function (Blueprint $table) {
                $table->boolean('IsPrimary')->default(1)->after('SubscribedOn');
            });
        }

        // Filtered/unique indexes and helper indexes (SQL Server)
        if (Schema::hasTable('t_MedicalFundContributors') && Schema::hasColumn('t_MedicalFundContributors', 'FundID') && Schema::hasColumn('t_MedicalFundContributors', 'ContributorNo')) {
            DB::statement("IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_MedFundContrib_Fund_ContributorNo') CREATE UNIQUE INDEX IX_MedFundContrib_Fund_ContributorNo ON t_MedicalFundContributors (FundID, ContributorNo) WHERE ContributorNo IS NOT NULL");
        }
        if (Schema::hasTable('t_MedicalFundBeneficiaries') && Schema::hasColumn('t_MedicalFundBeneficiaries', 'ContributorID')) {
            DB::statement("IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_MedFundBeneficiaries_Contributor') CREATE INDEX IX_MedFundBeneficiaries_Contributor ON t_MedicalFundBeneficiaries (ContributorID)");
        }
        if (Schema::hasTable('t_MedicalFundContributions') && Schema::hasColumn('t_MedicalFundContributions', 'ContributorID') && Schema::hasColumn('t_MedicalFundContributions', 'ContributionDate')) {
            DB::statement("IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_MedFundContribs_Contributor') CREATE INDEX IX_MedFundContribs_Contributor ON t_MedicalFundContributions (ContributorID, ContributionDate)");
        }
        if (Schema::hasTable('t_MedicalFundDisbursements')) {
            if (Schema::hasColumn('t_MedicalFundDisbursements', 'ContributorID')) {
                DB::statement("IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_MFD_Contributor') CREATE INDEX IX_MFD_Contributor ON t_MedicalFundDisbursements (ContributorID)");
            }
            if (Schema::hasColumn('t_MedicalFundDisbursements', 'CoverageID')) {
                DB::statement("IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_MFD_Coverage') CREATE INDEX IX_MFD_Coverage ON t_MedicalFundDisbursements (CoverageID)");
            }
            if (Schema::hasColumn('t_MedicalFundDisbursements', 'PackageID')) {
                DB::statement("IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_MFD_Package') CREATE INDEX IX_MFD_Package ON t_MedicalFundDisbursements (PackageID)");
            }
        }
        if (Schema::hasTable('t_MedicalFundPackageCoverages') && Schema::hasColumn('t_MedicalFundPackageCoverages', 'PackageID') && Schema::hasColumn('t_MedicalFundPackageCoverages', 'CoverageID')) {
            DB::statement("IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_PackageCoverage') CREATE UNIQUE INDEX UX_PackageCoverage ON t_MedicalFundPackageCoverages (PackageID, CoverageID) WHERE IsActive = 1");
        }
        if (Schema::hasTable('t_MedicalFundContributorPackages') && Schema::hasColumn('t_MedicalFundContributorPackages', 'ContributorID') && Schema::hasColumn('t_MedicalFundContributorPackages', 'IsPrimary') && Schema::hasColumn('t_MedicalFundContributorPackages', 'IsActive')) {
            DB::statement("IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'UX_Contrib_Primary') CREATE UNIQUE INDEX UX_Contrib_Primary ON t_MedicalFundContributorPackages (ContributorID) WHERE IsPrimary = 1 AND IsActive = 1");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('t_MedicalFundPackageCoverages');
        Schema::dropIfExists('t_MedicalFundContributorPackages');
        Schema::dropIfExists('t_MedicalFundPackages');
        Schema::dropIfExists('t_MedicalFundDisbursements');
        Schema::dropIfExists('t_MedicalFundContributions');
        Schema::dropIfExists('t_MedicalFundBeneficiaries');
        Schema::dropIfExists('t_MedicalFundContributors');
        Schema::dropIfExists('t_Coverages');
        Schema::dropIfExists('t_BeneficiaryRelationships');
        Schema::dropIfExists('t_MedicalFunds');
    }
};
