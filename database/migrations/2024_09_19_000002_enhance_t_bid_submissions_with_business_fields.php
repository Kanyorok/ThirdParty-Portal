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
            // Add structured bid business fields
            $table->decimal('BidAmount', 15, 2)->nullable()->after('SupplierId');
            $table->string('Currency', 3)->nullable()->after('BidAmount');
            $table->integer('ValidityPeriod')->nullable()->comment('Days')->after('Currency');
            $table->integer('DeliveryPeriod')->nullable()->comment('Days')->after('ValidityPeriod');
            $table->text('PaymentTerms')->nullable()->after('DeliveryPeriod');
            $table->enum('BidStatus', ['draft', 'submitted', 'responsive', 'non-responsive', 'evaluated', 'awarded', 'rejected'])
                  ->default('submitted')->after('PaymentTerms');
            
            // Evaluation and scoring fields
            $table->decimal('TechnicalScore', 5, 2)->nullable()->after('BidStatus');
            $table->decimal('FinancialScore', 5, 2)->nullable()->after('TechnicalScore');
            $table->decimal('TotalScore', 5, 2)->nullable()->after('FinancialScore');
            $table->boolean('IsResponsive')->nullable()->after('TotalScore');
            $table->text('ResponsivenessRemarks')->nullable()->after('IsResponsive');
            $table->text('EvaluationNotes')->nullable()->after('ResponsivenessRemarks');
            
            // Opening ceremony tracking
            $table->dateTime('OpenedAt')->nullable()->after('EvaluationNotes');
            $table->foreignId('OpenedBy')->nullable()->constrained('t_Users', 'Id')->after('OpenedAt');
            
            // Add index for better performance
            $table->index(['TenderRef', 'BidStatus']);
            $table->index(['IsResponsive', 'BidStatus']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('t_BidSubmissions')) {
            return;
        }
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            $table->dropIndex(['TenderRef', 'BidStatus']);
            $table->dropIndex(['IsResponsive', 'BidStatus']);
            
            $table->dropForeign(['OpenedBy']);
            $table->dropColumn([
                'OpenedBy',
                'OpenedAt',
                'EvaluationNotes',
                'ResponsivenessRemarks',
                'IsResponsive',
                'TotalScore',
                'FinancialScore',
                'TechnicalScore',
                'BidStatus',
                'PaymentTerms',
                'DeliveryPeriod',
                'ValidityPeriod',
                'Currency',
                'BidAmount'
            ]);
        });
    }
};
