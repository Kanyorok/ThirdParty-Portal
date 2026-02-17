<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRPayrollDeductionRules', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('DeductionID');
            $table->string('CalcMethod', 50)->default('PercentageOnGross'); // PercentageOnGross, Flat, PercentageOnBand, FlatOnBand
            $table->decimal('Rate', 9, 4)->nullable();
            $table->decimal('Amount', 18, 2)->nullable();
            $table->decimal('IncomeFrom', 18, 2)->default(0);
            $table->decimal('IncomeTo', 18, 2)->nullable();
            $table->decimal('MinAmount', 18, 2)->nullable();
            $table->decimal('MaxAmount', 18, 2)->nullable();
            $table->boolean('HasRelief')->default(false);
            $table->string('ReliefType', 20)->nullable(); // Percentage or Fixed
            $table->decimal('ReliefRate', 9, 4)->nullable();
            $table->decimal('ReliefAmount', 18, 2)->nullable();
            $table->date('EffectiveFrom');
            $table->date('EffectiveTo')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRPayrollDeductionRules');
    }
};
