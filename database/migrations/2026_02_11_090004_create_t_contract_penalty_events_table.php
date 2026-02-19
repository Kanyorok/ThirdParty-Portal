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
        Schema::create('t_ContractPenaltyEvents', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('FinanceInvoiceID')
                ->constrained('t_FinanceInvoiceEntry', 'Id')
                ->cascadeOnDelete();
            $table->foreignId('MilestoneID')
                ->nullable()
                ->constrained('t_ContractMilestones', 'Id')
                ->nullOnDelete();
            $table->foreignId('PenaltyRuleID')
                ->nullable()
                ->constrained('t_ContractPenaltyRules', 'Id')
                ->nullOnDelete();
            $table->decimal('ComputedAmount', 18, 2)->default(0);
            $table->decimal('AppliedAmount', 18, 2)->default(0);
            $table->enum('Status', ['Computed', 'Applied', 'Waived'])->default('Computed');
            $table->foreignId('ActionBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ActionOn')->nullable();
            $table->text('Reason')->nullable();
            $table->timestamps();

            $table->index(['FinanceInvoiceID', 'Status'], 'idx_penalty_events_invoice_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ContractPenaltyEvents');
    }
};

