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

        // Add missing business fields if absent (caused by earlier migration ordering)
        if (!Schema::hasColumn('t_BidSubmissions', 'BidAmount')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->decimal('BidAmount', 15, 2)->nullable();
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'Currency')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->string('Currency', 3)->nullable();
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'ValidityPeriod')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->integer('ValidityPeriod')->nullable()->comment('Days');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'DeliveryPeriod')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->integer('DeliveryPeriod')->nullable()->comment('Days');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'PaymentTerms')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->text('PaymentTerms')->nullable();
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'BidStatus')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                // SQL Server-friendly: use string instead of enum to avoid platform edge cases
                $table->string('BidStatus', 50)->default('submitted');
            });
        }

        // Opening ceremony tracking
        if (!Schema::hasColumn('t_BidSubmissions', 'OpenedAt')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->dateTime('OpenedAt')->nullable();
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'OpenedBy')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->foreignId('OpenedBy')->nullable()->constrained('t_Users', 'Id');
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'ReceivedOnTime')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->boolean('ReceivedOnTime')->nullable();
            });
        }

        // Evaluation and responsiveness fields
        if (!Schema::hasColumn('t_BidSubmissions', 'TechnicalScore')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->decimal('TechnicalScore', 5, 2)->nullable();
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'FinancialScore')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->decimal('FinancialScore', 5, 2)->nullable();
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'TotalScore')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->decimal('TotalScore', 5, 2)->nullable();
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'IsResponsive')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->boolean('IsResponsive')->nullable();
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'ResponsivenessRemarks')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->text('ResponsivenessRemarks')->nullable();
            });
        }
        if (!Schema::hasColumn('t_BidSubmissions', 'EvaluationNotes')) {
            Schema::table('t_BidSubmissions', function (Blueprint $table) {
                $table->text('EvaluationNotes')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('t_BidSubmissions')) {
            return;
        }
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            if (Schema::hasColumn('t_BidSubmissions', 'OpenedBy')) {
                $table->dropConstrainedForeignId('OpenedBy');
            }
        });
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            foreach ([
                'BidAmount', 'Currency', 'ValidityPeriod', 'DeliveryPeriod', 'PaymentTerms', 'BidStatus',
                'OpenedAt', 'OpenedBy', 'ReceivedOnTime',
                'TechnicalScore', 'FinancialScore', 'TotalScore', 'IsResponsive', 'ResponsivenessRemarks', 'EvaluationNotes',
            ] as $col) {
                if (Schema::hasColumn('t_BidSubmissions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};


