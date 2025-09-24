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
        Schema::table('t_FinanceCreditManagement', function (Blueprint $table) {
            // Risk assessment fields
            $table->integer('RiskScore')->default(50)->after('ApprovalReason')->comment('Risk score 0-100 (0=lowest risk, 100=highest risk)');
            $table->enum('RiskLevel', ['Low', 'Medium', 'High'])->default('Medium')->after('RiskScore');
            $table->integer('ReviewCycleMonths')->default(6)->after('RiskLevel')->comment('How often to review this credit in months');
            $table->date('LastReviewDate')->nullable()->after('ReviewCycleMonths');
            $table->date('NextReviewDate')->nullable()->after('LastReviewDate');
            $table->text('RiskNotes')->nullable()->after('NextReviewDate')->comment('Risk assessment notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceCreditManagement', function (Blueprint $table) {
            $table->dropColumn([
                'RiskScore', 
                'RiskLevel', 
                'ReviewCycleMonths', 
                'LastReviewDate', 
                'NextReviewDate', 
                'RiskNotes'
            ]);
        });
    }
};
