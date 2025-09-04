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
        Schema::create('t_BudgetLineLedgerLimits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('BudgetLineID')->constrained('t_BudgetLines');
            $table->unsignedBigInteger('LedgerID'); // CBS GL reference
            $table->string('LimitType'); // Monthly, Quarterly, Annual
            $table->decimal('LimitAmount',15,2);
            $table->date('EffectiveFrom');
            $table->date('EffectiveTo')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users');
            $table->timestamp('CreatedOn')->useCurrent();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users');
            $table->timestamp('ModifiedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::dropIfExists('t_BudgetLineLedgerLimits');
    }
};
