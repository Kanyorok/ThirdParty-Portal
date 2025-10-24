<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

echo "=== CREATING MISSING CRITICAL TABLES ===\n";

try {
    // Create t_Tenders table
    if (!Schema::hasTable('t_Tenders')) {
        echo "1. Creating t_Tenders table:\n";
        Schema::create('t_Tenders', function (Blueprint $table) {
            $table->id('Id');
            $table->string('TenderNo', 50)->unique();
            $table->string('Title', 255);
            $table->string('TenderType', 20)->default('Open');
            $table->string('TenderCategory', 20)->nullable();
            $table->text('ScopeOfWork')->nullable();
            $table->text('Instructions')->nullable();
            $table->date('SubmissionDeadline');
            $table->date('OpeningDate');
            $table->string('Status', 20)->default('draft');
            $table->decimal('EstimatedValue', 18, 2)->nullable();
            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->unsignedBigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->softDeletes('DeletedOn');

            $table->index(['Status']);
            $table->index(['TenderType']);
        });
        echo "   ✅ t_Tenders created\n";

        // Record migration
        DB::table('migrations')->insert([
            'migration' => '2025_04_17_093419_create_t__tenders_table',
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
    } else {
        echo "1. t_Tenders: ✅ Already exists\n";
    }

    // Create t_TenderSuppliers table
    if (!Schema::hasTable('t_TenderSuppliers')) {
        echo "2. Creating t_TenderSuppliers table:\n";
        Schema::create('t_TenderSuppliers', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('TenderID');
            $table->unsignedBigInteger('SupplierID');
            $table->dateTime('SubmissionDate')->nullable();
            $table->decimal('QuotedAmount', 18, 2)->nullable();
            $table->string('DeliveryTerms')->nullable();
            $table->string('PaymentTerms')->nullable();
            $table->text('Remarks')->nullable();
            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->unsignedBigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->softDeletes('DeletedOn');

            $table->index(['TenderID', 'SupplierID']);
        });
        echo "   ✅ t_TenderSuppliers created\n";

        // Record migration
        DB::table('migrations')->insert([
            'migration' => '2025_05_27_082424_create__tender_suppliers_table',
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
    } else {
        echo "2. t_TenderSuppliers: ✅ Already exists\n";
    }

    // Create t_BidResponsiveness table
    if (!Schema::hasTable('t_BidResponsiveness')) {
        echo "3. Creating t_BidResponsiveness table:\n";
        Schema::create('t_BidResponsiveness', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('TenderSupplierID');
            $table->boolean('SubmittedTimely')->default(false);
            $table->boolean('MandatoryDocuments')->default(false);
            $table->boolean('EligibilityCriteria')->default(false);
            $table->boolean('IsResponsive')->default(false);
            $table->text('Remarks')->nullable();
            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->unsignedBigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->softDeletes('DeletedOn');

            $table->index('TenderSupplierID');
            $table->index('IsResponsive');
        });
        echo "   ✅ t_BidResponsiveness created\n";

        // Record migration
        DB::table('migrations')->insert([
            'migration' => '2025_06_20_153514_create_bid_responsiveness_table',
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
    } else {
        echo "3. t_BidResponsiveness: ✅ Already exists\n";
    }

    // Create t_TenderAwards table
    if (!Schema::hasTable('t_TenderAwards')) {
        echo "4. Creating t_TenderAwards table:\n";
        Schema::create('t_TenderAwards', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('TenderID');
            $table->unsignedBigInteger('WinningSupplierID');
            $table->decimal('AwardedAmount', 18, 2)->nullable();
            $table->text('AwardJustification');
            $table->date('AwardDate');
            $table->date('ContractStartDate')->nullable();
            $table->date('ContractEndDate')->nullable();
            $table->decimal('TechnicalScore', 5, 2)->nullable();
            $table->decimal('FinancialScore', 5, 2)->nullable();
            $table->decimal('TotalScore', 5, 2)->nullable();
            $table->boolean('NotifyUnsuccessfulBidders')->default(true);
            $table->enum('AwardStatus', ['Pending', 'Approved', 'Rejected', 'Cancelled'])->default('Pending');
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->text('ApprovalRemarks')->nullable();
            $table->unsignedBigInteger('RejectedBy')->nullable();
            $table->dateTime('RejectedOn')->nullable();
            $table->text('RejectionReason')->nullable();
            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->unsignedBigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->softDeletes('DeletedOn');

            $table->index(['TenderID', 'AwardStatus']);
            $table->index('AwardDate');
            $table->index('AwardStatus');
        });
        echo "   ✅ t_TenderAwards created\n";

        // Record migration
        DB::table('migrations')->insert([
            'migration' => '2025_07_05_000000_create_tender_awards_table',
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
    } else {
        echo "4. t_TenderAwards: ✅ Already exists\n";
    }

    echo "\n✅ All critical tables created successfully!\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TABLE CREATION COMPLETE ===\n";
