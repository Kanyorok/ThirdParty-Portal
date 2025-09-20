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
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            // Add detailed responsiveness fields to match t_BidResponsiveness structure
            $table->boolean('SubmittedTimely')->nullable()->after('ReceivedOnTime');
            $table->boolean('HasMandatoryDocuments')->nullable()->after('SubmittedTimely');
            $table->boolean('IsEligible')->nullable()->after('HasMandatoryDocuments');
            
            // Enhanced responsiveness tracking
            $table->text('TimelySubmissionRemarks')->nullable()->after('IsEligible');
            $table->text('DocumentComplianceRemarks')->nullable()->after('TimelySubmissionRemarks');
            $table->text('EligibilityRemarks')->nullable()->after('DocumentComplianceRemarks');
            
            // Responsiveness check tracking
            $table->dateTime('ResponsivenessCheckedAt')->nullable()->after('EligibilityRemarks');
            $table->foreignId('ResponsivenessCheckedBy')->nullable()->constrained('t_Users', 'Id')->after('ResponsivenessCheckedAt');
            
            // Link to existing t_BidResponsiveness if needed for compatibility
            $table->foreignId('TenderSupplierID')->nullable()->after('ResponsivenessCheckedBy');
            
            // Add indexes
            $table->index(['SubmittedTimely', 'HasMandatoryDocuments', 'IsEligible']);
            $table->index('ResponsivenessCheckedAt');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('t_BidSubmissions')) {
            return;
        }
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            $table->dropIndex(['SubmittedTimely', 'HasMandatoryDocuments', 'IsEligible']);
            $table->dropIndex(['ResponsivenessCheckedAt']);
            
            $table->dropForeign(['ResponsivenessCheckedBy']);
            $table->dropColumn([
                'TenderSupplierID',
                'ResponsivenessCheckedBy',
                'ResponsivenessCheckedAt',
                'EligibilityRemarks',
                'DocumentComplianceRemarks',
                'TimelySubmissionRemarks',
                'IsEligible',
                'HasMandatoryDocuments',
                'SubmittedTimely'
            ]);
        });
    }
};
