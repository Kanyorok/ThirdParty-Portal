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
            $table->bigIncrements('Id');
            $table->bigInteger('BudgetID');
            $table->bigInteger('ReallocationID')->nullable();
            $table->bigInteger('BudgetLineID');
            $table->string('ERPLedgerID')->nullable();
            $table->string('LedgerID');
            $table->bigInteger('BranchID');
            $table->string('LimitType', 50)->nullable();
            $table->decimal('LimitAmount', 15);
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec073f6bb7e4');
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
