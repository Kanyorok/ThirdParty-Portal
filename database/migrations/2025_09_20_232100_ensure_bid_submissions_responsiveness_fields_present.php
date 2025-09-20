<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('t_BidSubmissions')) {
            return;
        }

        if (!Schema::hasColumn('t_BidSubmissions', 'SubmittedTimely')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->boolean('SubmittedTimely')->nullable()->after('ReceivedOnTime');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'HasMandatoryDocuments')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->boolean('HasMandatoryDocuments')->nullable()->after('SubmittedTimely');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'IsEligible')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->boolean('IsEligible')->nullable()->after('HasMandatoryDocuments');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'TimelySubmissionRemarks')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->text('TimelySubmissionRemarks')->nullable()->after('IsEligible');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'DocumentComplianceRemarks')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->text('DocumentComplianceRemarks')->nullable()->after('TimelySubmissionRemarks');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'EligibilityRemarks')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->text('EligibilityRemarks')->nullable()->after('DocumentComplianceRemarks');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'ResponsivenessCheckedAt')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->dateTime('ResponsivenessCheckedAt')->nullable()->after('EligibilityRemarks');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'ResponsivenessCheckedBy')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->foreignId('ResponsivenessCheckedBy')->nullable()->constrained('t_Users', 'Id')->after('ResponsivenessCheckedAt');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'TenderSupplierID')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->foreignId('TenderSupplierID')->nullable()->after('ResponsivenessCheckedBy');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('t_BidSubmissions')) {
            return;
        }
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            if (Schema::hasColumn('t_BidSubmissions', 'ResponsivenessCheckedBy')) {
                $table->dropConstrainedForeignId('ResponsivenessCheckedBy');
            }
        });
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            foreach ([
                'TenderSupplierID', 'ResponsivenessCheckedBy', 'ResponsivenessCheckedAt',
                'EligibilityRemarks', 'DocumentComplianceRemarks', 'TimelySubmissionRemarks',
                'IsEligible', 'HasMandatoryDocuments', 'SubmittedTimely'
            ] as $col) {
                if (Schema::hasColumn('t_BidSubmissions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
