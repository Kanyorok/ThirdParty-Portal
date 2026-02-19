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
        Schema::create('t_ContractPenaltyRules', function (Blueprint $table) {
            $table->id('Id');
            $table->string('ContractSourceType', 20); // tender | rfq
            $table->unsignedBigInteger('ContractSourceID');
            $table->foreignId('MilestoneID')
                ->nullable()
                ->constrained('t_ContractMilestones', 'Id')
                ->nullOnDelete();
            $table->enum('PenaltyType', ['PER_DAY_DELAY', 'PERCENT', 'FIXED'])->default('FIXED');
            $table->decimal('Rate', 18, 4)->nullable();
            $table->integer('GraceDays')->default(0);
            $table->decimal('CapAmount', 18, 2)->nullable();
            $table->decimal('CapPercent', 10, 4)->nullable();
            $table->enum('ApplyMethod', ['DEDUCT_FROM_PAYMENT', 'DEBIT_NOTE'])->default('DEDUCT_FROM_PAYMENT');
            $table->boolean('RequiresApprovalToApply')->default(false);
            $table->boolean('RequiresApprovalToWaive')->default(true);
            $table->boolean('IsActive')->default(true);
            $table->timestamps();

            $table->index(['ContractSourceType', 'ContractSourceID'], 'idx_contract_penalty_contract_ref');
            $table->index(['ContractSourceType', 'ContractSourceID', 'IsActive'], 'idx_contract_penalty_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ContractPenaltyRules');
    }
};

