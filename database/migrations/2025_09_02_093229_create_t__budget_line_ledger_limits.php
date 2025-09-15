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
            $table->unsignedBigInteger('BudgetID');
            $table->foreignId('ReallocationID')->nullable();
            $table->unsignedBigInteger('BudgetLineID');
            $table->string('ERPLedgerID')->nullable();  //ERP ID for ledgers sync with CBS
            $table->string('LedgerID');   // CBS GL reference
            $table->unsignedBigInteger('BranchID');   // 🔑 new branch link
            $table->string('LimitType', 50)->nullable();          // Monthly, Quarterly, Annual
            $table->decimal('LimitAmount', 15, 2);
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });

        // Foreign keys
        Schema::table('t_BudgetLineLedgerLimits', function (Blueprint $table) {
            $table->foreign('BudgetLineID', 'FK_BudgetLineLedgerLimits_BudgetLines')
                ->references('Id')->on('t_BudgetLines');

            $table->foreign('BranchID', 'FK_BudgetLineLedgerLimits_Branches')
                ->references('Id')->on('t_Branches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetLineLedgerLimits', function (Blueprint $table) {
            $table->dropForeign('FK_BudgetLineLedgerLimits_BudgetLines');
            $table->dropForeign('FK_BudgetLineLedgerLimits_Branches');
        });

        Schema::dropIfExists('t_BudgetLineLedgerLimits');
    }
};
 